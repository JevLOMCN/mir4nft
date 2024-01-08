<?php

namespace RPurinton\Mir4nft\Consumers;

use React\EventLoop\{LoopInterface, TimerInterface};
use RPurinton\Mir4nft\{Log, MySQL, HTTPS, Error};
use RPurinton\Mir4nft\RabbitMQ\Publisher;

class NewListingsConsumer
{
    private int $max_seq = 0;
    private string $wemix_url = "https://api.mir4global.com/wallet/prices/draco/daily";
    private array $wemix_data = [];
    private string $completed_url = "https://webapi.mir4global.com/nft/lists?"
        . "listType=recent&page=1&class=0&levMin=0&levMax=0"
        . "&powerMin=0&powerMax=0&priceMin=0&priceMax=0&languageCode=en";
    private string $base_url = "https://webapi.mir4global.com/nft/";
    private string $lists_url = "lists?";
    private array $http_query = [
        'listType' => 'sale',
        'class' => 0,
        'levMin' => 0,
        'levMax' => 0,
        'powerMin' => 0,
        'powerMax' => 0,
        'priceMin' => 0,
        'priceMax' => 0,
        'sort' => 'latest',
        'page' => 1,
        'languageCode' => 'en',
    ];
    private string $stats_url = "character/";
    private array $stat_checks = [
        "summary", // special case
        "inven", "skills", "stats", "spirit",
        "magicorb", "magicstone", "mysticalpiece", "building",
        "training", "holystuff", "assets", "potential", "codex",
        "priceeval", // special case
    ];
    private ?Publisher $pub = null;

    public function __construct(
        private Log $log,
        private MySQL $sql,
        private LoopInterface $loop
    ) {
        $log->debug("NewListingsConsumer created");
    }

    public function init(): bool
    {
        $this->log->debug("initializing NewListingsConsumer");
        $this->timer_300();
        $this->timer_5();
        $result1 = $this->loop->addPeriodicTimer(5, [$this, 'timer_5']) or throw new Error("failed to add periodic timer");
        $result2 = $this->loop->addPeriodicTimer(300, [$this, 'timer_300']) or throw new Error("failed to add periodic timer");
        $success = $result1 instanceof TimerInterface && $result2 instanceof TimerInterface;
        if ($success) $this->log->info("periodic timers added");
        else $this->log->error("failed to add periodic timers");
        return $success;
    }

    public function timer_5(): void
    {
        $this->log->debug("timer_5 fired");
        $this->update_max_seq();
        $url = $this->base_url . $this->lists_url . http_build_query($this->http_query);
        $response = HTTPS::get($url) or throw new Error("failed to get url");
        $data = json_decode($response, true) or throw new Error("failed to decode response", [$response]);
        $this->validate_data($data) or throw new Error("received invalid response", [$data]);
        $this->process_listings($data['data']['lists']) or throw new Error("failed to process listings");
        $this->check_completed() or throw new Error("failed to check completed");
    }

    public function timer_300(): void
    {
        $this->log->debug("timer_300 fired");
        $response = HTTPS::post($this->wemix_url, ["Content-type: application/json"]) or throw new Error("failed to get response");
        $this->update_wemix_data($response) or throw new Error("failed to update wemix data");
    }

    private function validate_data($data): bool
    {
        $this->log->debug("validating data");
        return is_array($data) && isset($data['data']['lists']) && is_array($data['data']['lists']);
    }

    private function process_listings(array $listings): bool
    {
        $this->log->debug("processing listings");
        $new_listings = $this->filter_listings($listings);
        if (!count($new_listings)) return true;
        foreach (array_reverse($new_listings) as $listing) {
            $this->process_listing($listing) or throw new Error("failed to process listing");
        }
        $this->pub = null;
        return true;
    }

    private function filter_listings(array $listings): array
    {
        $this->log->debug("filtering listings");
        $new_listings = [];
        foreach ($listings as $listing) {
            if ($listing['seq'] <= $this->max_seq) continue;
            $new_listings[] = $listing;
        }
        return $new_listings;
    }

    private function process_listing(array $listing): bool
    {
        $this->log->debug("received new listing");
        $this->max_seq = max($listing['seq'], $this->max_seq);
        [$seq, $transportID, $class, $cancelling, $transport_exists_already] = $this->insert_records($listing) or throw new Error("failed to insert records");
        if ($transport_exists_already) {
            $this->stat_check($seq, $transportID, $class, "summary") or throw new Error("failed to publish stat check");
            return true;
        }
        $this->stat_checks($seq, $transportID, $class) or throw new Error("failed to publish stat checks");
        $this->log->debug("published stat checks", [$transportID]);
        return true;
    }

    private function insert_records($listing): array
    {
        $this->log->debug("inserting records");
        extract($this->sql->escape($listing)) or throw new Error("failed to extract escaped listing");

        $result = $this->sql->query("SELECT `seq` FROM `sequence` WHERE `transportID` = '$transportID' AND `tradeType` = '1';") or throw new Error("failed to get sequence");
        if ($result->num_rows) {
            while ($row = $result->fetch_assoc()) $cancelling[] = $row['seq'];
            $this->sql->query("UPDATE `sequence` SET `tradeType` = '2' WHERE `transportID` = '$transportID' AND `tradeType` = '1';") or throw new Error("failed to cancel sequence");
        }

        extract($this->sql->single("SELECT count(1) as `transport_exists_already` FROM `transports` WHERE `transportID` = '$transportID';")) or throw new Error("failed to get transport count");
        if (!$transport_exists_already) {
            $this->log->debug("transport does not exist already", [$transportID]);
            $this->sql->query("INSERT INTO `transports` (
                `transportID`, `nftID`, `sealedDT`,
                `characterName`, `class`, `lv`, `powerScore`
            ) VALUES (
                '$transportID', '$nftID', '$sealedDT',
                '$characterName', '$class', '$lv', '$powerScore'
            );") or throw new Error("failed to insert transport");
        }

        $this->sql->query("INSERT INTO `sequence` (
            `seq`, `transportID`, `price`,
            `MirageScore`, `MiraX`, `Reinforce`
        ) VALUES (
            '$seq', '$transportID', '$price',
            '$MirageScore', '$MiraX', '$Reinforce'
        ) ON DUPLICATE KEY UPDATE
            `price` = '$price',
            `MirageScore` = '$MirageScore',
            `MiraX` = '$MiraX',
            `Reinforce` = '$Reinforce';") or throw new Error("failed to insert sequence");
        $this->log->debug("inserted new listing");
        $this->stat_check($seq, $transportID, $class, "notify") or throw new Error("failed to publish notificaton");
        return [$seq, $transportID, $class, $cancelling ?? [], $transport_exists_already ?? false];
    }

    private function stat_checks($seq, $transportID, $class): bool
    {
        $this->log->debug("publishing stat checks", [$transportID]);
        foreach ($this->stat_checks as $stat_check) {
            $this->stat_check($seq, $transportID, $class, $stat_check) or throw new Error("failed to publish stat check");
        }
        return true;
    }

    private function stat_check($seq, $transportID, $class, $stat_check): bool
    {
        $this->log->debug("stat check", [$stat_check]);
        if ($stat_check === "priceeval" || $stat_check === "notify") $count = 0;
        elseif ($stat_check !== "summary") extract($this->sql->single("SELECT count(1) as `count` FROM `$stat_check` WHERE `transportID` = '$transportID';")) or throw new Error("failed to get stat check count");
        else extract($this->sql->single("SELECT count(1) as `count` FROM `$stat_check` WHERE `seq` = '$seq';")) or throw new Error("failed to get stat check count");
        if ($count) return true;
        $payload = [
            'seq' => $seq,
            'transportID' => $transportID,
            'stat_check' => $stat_check,
            'stat_url' => $this->base_url . $this->stats_url . $stat_check . '?' . http_build_query([
                'seq' => $seq,
                'transportID' => $transportID,
                'class' => $class,
                'languageCode' => 'en',
            ])
        ];
        $this->log->debug("publishing stat check", [$payload]);
        if (!$this->pub) $this->pub = new Publisher() or throw new Error("failed to create Publisher");
        $this->pub->publish('stat_checker', $payload) or throw new Error("failed to publish stat check");
        return true;
    }
    private function check_completed(): bool
    {
        $this->log->debug("checking completed");
        $response = HTTPS::get($this->completed_url) or throw new Error("failed to get completed");
        $data = json_decode($response, true);
        $this->validate_completed($data) or throw new Error("received invalid response");
        $this->process_completed($data['data']['lists']) or throw new Error("failed to process completed");
        return true;
    }

    private function validate_completed($data): bool
    {
        $this->log->debug("validating completed");
        return is_array($data) && isset($data['data']['lists']) && is_array($data['data']['lists']);
    }

    private function process_completed(array $listings): bool
    {
        $this->log->debug("processing completed");
        foreach ($listings as $listing) {
            $seq = $listing['info']['seq'];
            $result = $this->sql->query("SELECT `seq`,`transportID` FROM `sequence` WHERE `tradeType` IN ('1', '2') AND `seq` = '$seq';") or throw new Error("failed to get pending sales");
            if ($result->num_rows > 0) {
                $this->process_completed_listing($listing['info']) or throw new Error("failed to process completed listing");
            }
        }
        return true;
    }

    private function process_completed_listing(array $listing): bool
    {
        $this->log->debug("received completed listing", [$listing]);
        $usd_price = $this->get_usd_price($listing);
        $seq = $this->sql->escape($listing['seq']);
        $timestamp = $this->sql->escape($listing['tradeDT']);
        $query = "UPDATE `sequence` SET `tradeType` = '3', `usd_price` = '$usd_price', `tradeDT` = '$timestamp' WHERE `seq` = '$seq';";
        $this->sql->query($query) or throw new Error("failed to update sequence");
        return true;
    }
    private function get_usd_price($listing)
    {
        $this->log->debug("getting usd price", [$listing]);
        $timestamp = $listing['tradeDT'];
        $wemix_rate = $this->get_wemix_rate($timestamp);
        $usd_price = $listing['price'] * $wemix_rate;
        $usd_price = round($usd_price, 2);
        return $usd_price;
    }

    private function get_wemix_rate($timestamp)
    {
        $this->log->debug("getting wemix rate", [$timestamp]);
        foreach ($this->wemix_data as $CreatedDT => $USDWemixRate) {
            if ($timestamp >= $CreatedDT) return $USDWemixRate;
        }
        return $USDWemixRate;
    }

    private function update_wemix_data($response)
    {
        $this->log->debug("updating wemix data");
        $data = json_decode($response, true);
        $this->validate_wemix_data($data) or throw new Error("received invalid response");
        $this->parse_wemix_data($data) or throw new Error("failed to parse wemix data");
        return true;
    }

    private function validate_wemix_data($data): bool
    {
        $this->log->debug("validating wemix data");
        return is_array($data) && isset($data['Data']) && is_array($data['Data']);
    }

    private function parse_wemix_data($data): bool
    {
        $this->log->debug("parsing wemix data");
        $wemix_data = [];
        foreach ($data['Data'] as $item) {
            $CreatedDT = strtotime($item['CreatedDT']);
            $USDWemixRate = $item['USDWemixRate'];
            $wemix_data[$CreatedDT] = $USDWemixRate;
        }
        $this->sql->query("INSERT INTO `wemix`
                (`CreatedDT`, `USDWemixRate`)
            VALUES
                ('$CreatedDT', '$USDWemixRate')
            ON DUPLICATE KEY UPDATE
                `USDWemixRate` = '$USDWemixRate';
        ") or throw new Error("failed to insert wemix data");
        $this->wemix_data = array_reverse($wemix_data);
        return true;
    }

    private function update_max_seq()
    {
        $this->log->debug("updating max seq");
        extract($this->sql->single("SELECT MAX(`seq`) as `max_seq` FROM `sequence`;")) or throw new Error("failed to get max seq");
        $this->max_seq = $max_seq;
    }
}
