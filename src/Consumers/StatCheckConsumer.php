<?php

namespace RPurinton\Mir4nft\Consumers;

use Bunny\{Channel, Message};
use React\EventLoop\LoopInterface;
use RPurinton\Mir4nft\{Log, MySQL, HTTPS, Error};
use RPurinton\Mir4nft\RabbitMQ\Consumer;
use RPurinton\Mir4nft\OpenAI\Client;

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
        $this->log->debug("inserted stats", [$query]);
        return true;
    }

    private function price_eval($seq, $transportID): bool
    {
        $this->log->info("waiting for all stats to be available");
        $retries = 0;
        while (true) {
            if ($retries++ > 60) throw new Error("timed out waiting for stats");
            $query = "SELECT count(1) as `ready`
                FROM `transports`
                INNER JOIN `assets` ON `transports`.`transportID` = `assets`.`transportID`
                INNER JOIN `building` ON `transports`.`transportID` = `building`.`transportID`
                INNER JOIN `codex` ON `transports`.`transportID` = `codex`.`transportID`
                INNER JOIN `holystuff` ON `transports`.`transportID` = `holystuff`.`transportID`
                INNER JOIN `inven` ON `transports`.`transportID` = `inven`.`transportID`
                INNER JOIN `magicorb` ON `transports`.`transportID` = `magicorb`.`transportID`
                INNER JOIN `magicstone` ON `transports`.`transportID` = `magicstone`.`transportID`
                INNER JOIN `mysticalpiece` ON `transports`.`transportID` = `mysticalpiece`.`transportID`
                INNER JOIN `potential` ON `transports`.`transportID` = `potential`.`transportID`
                INNER JOIN `skills` ON `transports`.`transportID` = `skills`.`transportID`
                INNER JOIN `spirit` ON `transports`.`transportID` = `spirit`.`transportID`
                INNER JOIN `stats` ON `transports`.`transportID` = `stats`.`transportID`
                INNER JOIN `training` ON `transports`.`transportID` = `training`.`transportID`
                INNER JOIN `summary` ON `summary`.`seq` = $seq
                WHERE `transports`.`transportID` = $transportID";
            extract($this->sql->single($query));
            if ($ready) break;
            sleep(1);
        }
        $this->log->info("all stats are available");
        return true;
    }
}
