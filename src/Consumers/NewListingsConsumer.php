<?php

namespace RPurinton\Mir4nft\Consumers;

use Bunny\{Async\Client, Channel, Message};
use React\EventLoop\{LoopInterface, TimerInterface};
use RPurinton\Mir4nft\{Log, MySQL, HTTPS, Error};
use RPurinton\Mir4nft\RabbitMQ\{Consumer, Publisher};

class NewListingsConsumer
{
    private int $max_seq = 0;
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
        "summary", "inven", "skills", "stats", "spirit",
        "magicorb", "magicstone", "mysticalpiece", "building",
        "training", "holystuff", "assets", "potential", "codex"
    ];
    private ?Publisher $pub = null;

    public function __construct(private Log $log, private MySQL $sql, private LoopInterface $loop)
    {
    }

    public function init(): bool
    {
        $this->timer();
        $result = $this->loop->addPeriodicTimer(15, [$this, 'timer']) or throw new Error("failed to add periodic timer");
        $success = $result instanceof TimerInterface;
        if ($success) $this->log->info("periodic timer added");
        else $this->log->error("failed to add periodic timer");
        return $success;
    }

    private function update_max_seq()
    {
        extract($this->sql->single("SELECT MAX(`seq`) as `max_seq` FROM `sequence`;")) or throw new Error("failed to get max seq");
        $this->max_seq = $max_seq;
    }

    public function timer(): void
    {
        $this->log->debug("timer fired");
        $this->update_max_seq();
        $url = $this->base_url . $this->lists_url . http_build_query($this->http_query);
        $response = HTTPS::get($url) or throw new Error("failed to get url");
        $data = json_decode($response, true);
        $this->validate_data($data) or throw new Error("received invalid response");
        $this->process_listings($data['data']['lists']) or throw new Error("failed to process listings");
    }

    private function validate_data($data): bool
    {
        return is_array($data) && isset($data['data']['lists']) && is_array($data['data']['lists']);
    }

    private function process_listings(array $listings): bool
    {
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
        $new_listings = [];
        foreach ($listings as $listing) {
            if ($listing['seq'] <= $this->max_seq) continue;
            $new_listings[] = $listing;
        }
        return $new_listings;
    }

    private function process_listing(array $listing): bool
    {
        $this->log->debug("received new listing", [$listing]);
        $this->max_seq = max($listing['seq'], $this->max_seq);
        [$seq, $transportID] = $this->insert_records($listing) or throw new Error("failed to insert records");
        $this->stat_checks($seq, $transportID) or throw new Error("failed to publish stat checks");
        $this->log->debug("published stat checks", [$transportID]);
        return true;
    }

    private function insert_records($listing): array
    {
        extract($this->sql->escape($listing)) or throw new Error("failed to extract escaped listing");
        $query = "INSERT INTO `transports` (
                `transportID`, `nftID`, `sealedDT`, `characterName`,
                `class`, `lv`, `powerScore`, `price`,
                `MirageScore`, `MiraX`, `Reinforce`
            ) VALUES (
                '$transportID', '$nftID', '$sealedDT', '$characterName',
                '$class', '$lv', '$powerScore', '$price',
                '$MirageScore', '$MiraX', '$Reinforce'
            ) ON DUPLICATE KEY UPDATE
                `nftID` = '$nftID',
                `sealedDT` = '$sealedDT',
                `characterName` = '$characterName',
                `class` = '$class',
                `lv` = '$lv',
                `powerScore` = '$powerScore',
                `price` = '$price',
                `MirageScore` = '$MirageScore',
                `MiraX` = '$MiraX',
                `Reinforce` = '$Reinforce';
            INSERT INTO `sequence` (
                `seq`, `transportID`
            ) VALUES (
                '$seq', '$transportID'
            ) ON DUPLICATE KEY UPDATE `seq` = `seq`;";
        $this->log->debug("inserting new listing", [$query]);
        $this->sql->multi($query);
        return [$seq, $transportID];
    }

    private function stat_checks($seq, $transportID): bool
    {
        $this->log->debug("publishing stat checks", [$transportID]);
        foreach ($this->stat_checks as $stat_check) {
            $this->stat_check($seq, $transportID, $stat_check) or throw new Error("failed to publish stat check");
        }
        return true;
    }

    private function stat_check($seq, $transportID, $stat_check): bool
    {
        extract($this->sql->single("SELECT count(1) as `count` FROM `$stat_check` WHERE `transportID` = '$transportID';")) or throw new Error("failed to get stat check count");
        if ($count) return true;
        $payload = [
            'seq' => $seq,
            'transportID' => $transportID,
            'stat_check' => $stat_check,
            'stat_url' => $this->base_url . $this->stats_url . $stat_check . '?' . http_build_query([
                'seq' => $seq,
                'transportID' => $transportID,
                'languageCode' => 'en',
            ])
        ];
        $this->log->debug("publishing stat check", [$payload]);
        if (!$this->pub) $this->pub = new Publisher() or throw new Error("failed to create Publisher");
        $this->pub->publish('stat_checker', $payload) or throw new Error("failed to publish stat check");
        return true;
    }
}
