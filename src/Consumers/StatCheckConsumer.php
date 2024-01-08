<?php

namespace RPurinton\Mir4nft\Consumers;

use Bunny\{Channel, Message};
use React\EventLoop\LoopInterface;
use RPurinton\Mir4nft\{Log, MySQL, HTTPS, Error, Export, DealText};
use RPurinton\Mir4nft\RabbitMQ\Consumer;
use RPurinton\Mir4nft\OpenAI\Client;
use RPurinton\Mir4nft\Discord\Webhook;

class StatCheckConsumer
{
    public function __construct(
        private Log $log,
        private LoopInterface $loop,
        private MySQL $sql,
        private Consumer $mq,
        private Client $ai,
    ) {
        $log->debug("constructing stat check consumer");
    }

    public function init(): bool
    {
        $this->sql->connect() or throw new Error("failed to connect to MySQL");
        $this->mq->connect($this->loop, "stat_checker", $this->stats_callback(...)) or throw new Error("failed to connect to stat_check queue");
        return true;
    }

    public function stats_callback(Message $message, Channel $channel): bool
    {
        $this->log->debug("received stats callback", [$message->content]);
        $data = json_decode($message->content, true);
        $this->validate_stats_callback($data) or throw new Error("received invalid stats callback");
        $this->insert_stats($data) or throw new Error("failed to insert stats");
        $channel->ack($message);
        return true;
    }

    private function validate_stats_callback($data): bool
    {
        return is_array($data) &&
            isset($data['seq']) &&
            isset($data['transportID']) &&
            isset($data['stat_check']) &&
            isset($data['stat_url']);
    }

    private function insert_stats($data): bool
    {
        extract($data) or throw new Error("failed to extract data");
        if ($stat_check === 'notify') return $this->notify($seq, $transportID);
        if ($stat_check === 'priceeval') return $this->price_eval($seq, $transportID);
        $response = HTTPS::get($stat_url) or throw new Error("failed to get url");
        $response_escaped = $this->sql->escape($response);
        if ($stat_check !== "summary") {
            $query = "INSERT INTO `$stat_check` (
                `transportID`, `json`
            ) VALUES (
                '$transportID', '$response_escaped'
            ) ON DUPLICATE KEY UPDATE `json` = '$response_escaped';\n";
        } else {
            $query = "INSERT INTO `summary` (
                `seq`, `json`
            ) VALUES (
                '$seq', '$response_escaped'
            ) ON DUPLICATE KEY UPDATE `json` = '$response_escaped';\n";
            $response_data = json_decode($response, true)['data'] ?? null;
            if ($response_data) {
                $tradeType = $response_data['tradeType'] ?? null;
                if ($tradeType) {
                    $tradeType_escaped = $this->sql->escape($tradeType);
                    $query .= "UPDATE `sequence` SET `tradeType` = '$tradeType_escaped' WHERE `seq` = '$seq';\n";
                    if ($tradeType == 3) {
                        $tradeDT = $response_data['tradeDT'] ?? null;
                        if ($tradeDT) {
                            $tradeDT_escaped = $this->sql->escape($tradeDT);
                            $query .= "UPDATE `sequence` SET `tradeDT` = '$tradeDT_escaped' WHERE `seq` = '$seq' AND `tradeDT` IS NULL;\n";
                        }
                    }
                }
            }
        }
        $this->sql->multi($query);
        $this->log->debug("inserted $stat_check", [$transportID]);
        return true;
    }

    private function wait_stats($seq, $transport): \mysqli_result
    {
        $this->log->debug("waiting for all stats to be available");
        $retries = 0;
        while (true) {
            if ($retries++ > 60) throw new Error("timed out waiting for stats");
            $result = $this->sql->query("SELECT 
                    `sequence`.`price` AS `ask_wemix`,
                    `transports`.`lv` AS `lv`,
                    `transports`.`class` AS `class`,
                    `transports`.`powerScore` AS `powerScore`,
                    `summary`.`json` AS `summary`,
                    `assets`.`json` AS `assets`,
                    `building`.`json` AS `building`,
                    `codex`.`json` AS `codex`,
                    `holystuff`.`json` AS `holystuff`,
                    `magicorb`.`json` AS `magicorb`,
                    `mysticalpiece`.`json` AS `mysticalpiece`,
                    `potential`.`json` AS `potential`,
                    `skills`.`json` AS `skills`,
                    `spirit`.`json` AS `spirit`,
                    `stats`.`json` AS `stats`,
                    `training`.`json` AS `training`
                FROM `sequence`
                INNER JOIN `summary` ON `sequence`.`seq` = `summary`.`seq`
                INNER JOIN `assets` ON `sequence`.`transportID` = `assets`.`transportID`
                INNER JOIN `building` ON `sequence`.`transportID` = `building`.`transportID`
                INNER JOIN `codex` ON `sequence`.`transportID` = `codex`.`transportID`
                INNER JOIN `holystuff` ON `sequence`.`transportID` = `holystuff`.`transportID`
                INNER JOIN `magicorb` ON `sequence`.`transportID` = `magicorb`.`transportID`
                INNER JOIN `mysticalpiece` ON `sequence`.`transportID` = `mysticalpiece`.`transportID`
                INNER JOIN `potential` ON `sequence`.`transportID` = `potential`.`transportID`
                INNER JOIN `skills` ON `sequence`.`transportID` = `skills`.`transportID`
                INNER JOIN `spirit` ON `sequence`.`transportID` = `spirit`.`transportID`
                INNER JOIN `stats` ON `sequence`.`transportID` = `stats`.`transportID`
                INNER JOIN `training` ON `sequence`.`transportID` = `training`.`transportID`
                INNER JOIN `transports` ON `sequence`.`transportID` = `transports`.`transportID`
                WHERE `sequence`.`transportID` = '$transport' AND `sequence`.`seq` = $seq
            ") or throw new Error("failed to get stats");
            if ($result->num_rows == 1) break;
            sleep(1);
        }
        $this->log->debug("all stats are available");
        return $result;
    }

    private function price_eval($seq, $transportID): bool
    {
        $result = $this->wait_stats($seq, $transportID);
        $row = $result->fetch_assoc();
        $record = Export::row($row);
        $record = json_encode($record) . "\n";
        $this->log->debug("record", [$record]);
        $result = $this->ai->complete($record);
        $this->log->debug("price eval result", [$result]);
        $result = str_replace(',', '', $result); // remove commas (e.g. 1,000)
        $result = json_decode($result, true);
        $price = $result['usd_price'] ?? null;
        if (!$price) throw new Error("failed to get price");
        $price_escaped = $this->sql->escape($price);
        $query = "INSERT INTO `evals` (`transportID`, `usd_price`) VALUES ('$transportID', '$price_escaped')";
        $this->sql->query($query);
        return true;
    }

    private function wait_eval($seq, $transportID): \mysqli_result
    {
        $this->log->debug("waiting for eval to be available");
        $retries = 0;
        while (true) {
            if ($retries++ > 60) throw new Error("timed out waiting for eval");
            $result = $this->sql->query("SELECT `usd_price` AS `value_usd` FROM `evals` WHERE `transportID` = '$transportID' ORDER BY `evalID` DESC LIMIT 1") or throw new Error("failed to get eval");
            if ($result->num_rows == 1) break;
            sleep(1);
        }
        $this->log->debug("eval is available");
        return $result;
    }

    private function notify($seq, $transportID): bool
    {
        $stats = $this->wait_stats($seq, $transportID);
        $eval = $this->wait_eval($seq, $transportID);
        extract($stats->fetch_assoc());
        extract($eval->fetch_assoc());
        $this->log->debug("notifying", [$seq, $transportID, $usd_price]);
        $ask_usd = $this->getUSD($ask_wemix);
        $value_wemix = $this->getWEMIX($value_usd);
        $diff_usd = $value_usd - $ask_usd;
        $diff_pct = round($diff_usd / $ask_usd * 100);
        $dealtext = DealText::get($diff_pct);
        $color = DealText::color($diff_pct);
        if ($diff_pct >= 20 && $ask_wemix <= 100) $content = "<@1192961374515642528>";
        new Webhook(['content' => $content ?? '', 'embeds' => [Webhook::embed($lv, $class, $powerScore, $dealtext, $seq, $color, $ask_wemix, $ask_usd, $value_wemix, $value_usd, $diff_pct, $diff_usd)]]);
        return true;
    }

    private function getWEMIX(int $usd): int
    {
        return (int)round($usd / $this->getWemixRate());
    }

    private function getUSD(int $wemix): int
    {
        return (int)round($wemix * $this->getWemixRate());
    }

    private function getWemixRate(): float
    {
        extract($this->sql->single("SELECT `USDWemixRate` FROM `wemix` ORDER BY `record_id` DESC LIMIT 1"));
        return $USDWemixRate ?? 0;
    }
}
