<?php

namespace RPurinton\Mir4nft\Consumers;

use React\EventLoop\{Loop, LoopInterface, TimerInterface};
use RPurinton\Mir4nft\{RabbitMQ\Publisher, Log, MySQL, Error};

class NewListingsConsumer
{
    private int $max_seq = 0;
    private array $stat_checks = [
        "summary", "inven", "skills", "stats", "spirit",
        "magicorb", "magicstone", "mysticalpiece", "building",
        "training", "holystuff", "assets", "potential", "codex"
    ];

    public function __construct(private Log $log, private MySQL $sql, private ?LoopInterface $loop = null)
    {
        $this->loop = $loop ?? Loop::get();
    }

    public function run(): bool
    {
        extract($this->sql->single("SELECT MAX(`seq`) as `max_seq` FROM `sequence`;")) or throw new Error("failed to get max seq");
        $this->max_seq = $max_seq;
        $result = $this->loop->addPeriodicTimer(15, $this->timer(...) or throw new Error("failed to add periodic timer"));
        return $result instanceof TimerInterface;
    }

    public function timer(): void
    {
        $url = "https://webapi.mir4global.com/nft/lists?listType=sale&class=0&levMin=0&levMax=0&powerMin=0&powerMax=0&priceMin=0&priceMax=0&sort=latest&page=1&languageCode=en";
        $message = @file_get_contents($url) or throw new Error("failed to get new listings");
        $data = json_decode($message, true);
        $this->validate_data($data) or throw new Error("received invalid message");
        $this->process_listings($data['data']['lists']) or throw new Error("failed to process listings");
    }

    private function process_listings($listings): void
    {
        $new_listings = [];
        foreach ($listings as $listing) {
            if ($listing['seq'] <= $this->max_seq) continue;
            $new_listings[] = $listing;
        }
        if (!count($new_listings)) return;
        $pub = new Publisher() or throw new Error("failed to create Publisher");
        foreach (array_reverse($new_listings) as $listing) {
            $this->process_listing($listing, $pub) or throw new Error("failed to process listing");
        }
        $pub->disconnect() or throw new Error("failed to disconnect from RabbitMQ");
        unset($new_listings);
        unset($pub);
    }

    private function process_listing($listing, $pub): bool
    {
        $this->max_seq = $listing['seq'];
        $this->log->debug("received new listing", [$listing]);
        [$seq, $transportID] = $this->insert_records($listing) or throw new Error("failed to insert records");
        foreach ($this->stat_checks as $stat_check) {
            $this->stat_checks($seq, $transportID, $pub) or throw new Error("failed to publish stat checks");
        }
        $this->log->debug("published stat checks", [$seq, $transportID]);
        return true;
    }

    private function insert_records($listing): array
    {
        $listing = $this->sql->escape($listing);
        extract($listing);
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

    private function stat_checks($seq, $transportID, $pub): bool
    {
        $this->log->debug("NewListingsConsumer published stat checks", [$seq, $transportID]);
        foreach ($this->stat_checks as $stat_check) {
            $payload = [
                'seq' => $seq,
                'transportID' => $transportID,
                'stat_check' => $stat_check
            ];
            $this->log->debug("NewListingsConsumer publishing stat check", [$payload]);
            $pub->publish('stat_checker', $payload) or throw new Error("failed to publish stat check");
        }
        return true;
    }

    private function validate_data($data): bool
    {
        return !is_null($data) && is_array($data) && isset($data['data']['lists']) && is_array($data['data']['lists']);
    }
}
