<?php

namespace RPurinton\Mir4nft\Consumers;

use React\EventLoop\{LoopInterface, TimerInterface};
use RPurinton\Mir4nft\{RabbitMQ\Publisher, Log, MySQL, Error};

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
        $result = $this->sql->query("SELECT MAX(`seq`) as `max_seq` FROM `sequence`;") or throw new Error("failed to get max seq step 1");
        if (!$result) throw new Error("failed to get max seq step 2");
        $result = $result->fetch_assoc() or throw new Error("failed to get max seq step 3");
        $this->max_seq = $result['max_seq'] ?? 0;
        $result = $this->loop->addPeriodicTimer(15, $this->timer(...) or throw new Error("failed to add periodic timer"));
        return $result instanceof TimerInterface;
    }

    public function timer(): void
    {
        $message = @file_get_contents($this->base_url . $this->lists_url . http_build_query($this->http_query));
        $data = json_decode($message, true);
        $this->validate_data($data) or throw new Error("received invalid message");
        $this->process_listings($data['data']['lists']) or throw new Error("failed to process listings");
    }

    private function validate_data($data): bool
    {
        return !is_null($data) && is_array($data) && isset($data['data']['lists']) && is_array($data['data']['lists']);
    }

    private function process_listings(array $listings): void
    {
        $new_listings = $this->filter_listings($listings);
        if (!count($new_listings)) return;
        foreach (array_reverse($new_listings) as $listing) {
            $this->process_listing($listing) or throw new Error("failed to process listing");
        }
        $this->pub = null;
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
        $this->max_seq = max($listing['seq'], $this->max_seq);
        $this->log->debug("received new listing", [$listing]);
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
            ) ON DUPLICATE KEY UPDATE `transportID` = `transportID`;
            INSERT INTO `sequence` (
                `seq`, `transportID`
            ) VALUES (
                '$seq', '$transportID'
            ) ON DUPLICATE KEY UPDATE `seq` = `seq`;";
        $this->log->debug("inserting new listing", [$query]);
        $this->sql->multi($query) or throw new Error("failed to insert new listing");
        return [$seq, $transportID];
    }

    private function stat_checks($seq, $transportID): bool
    {
        $this->log->debug("NewListingsConsumer published stat checks", [$transportID]);
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
        $this->log->debug("NewListingsConsumer publishing stat check", [$payload]);
        if (!$this->pub) $this->pub = new Publisher() or throw new Error("failed to create Publisher");
        $this->pub->publish('stat_checker', $payload) or throw new Error("failed to publish stat check");
        return true;
    }
}
