<?php

namespace RPurinton\Mir4nft\Consumers;

use React\Async;
use RPurinton\Mir4nft\{RabbitMQ, Log, MySQL, Error};
use Bunny\{Async\Client, Channel, Message};

class NewListingsConsumer
{
    private array $stat_checks = [
        "summary", "inven", "skills", "stats", "spirit",
        "magicorb", "magicstone", "mysticalpiece", "building",
        "training", "holystuff", "assets", "potential", "codex"
    ];

    public function __construct(private Log $log, private MySQL $sql, private RabbitMQ $mq)
    {
        $mq->connect("new_listings", $this->process(...)) or throw new Error("failed to connect to new_listings queue");
        $this->log->debug("NewListingsConsumer initialized!");
    }

    public function process(Message $message, Channel $channel, Client $client): void
    {
        $data = json_decode($message->content, true);
        if (!$this->validateData($data)) {
            $this->log->error("NewListingsConsumer received invalid message", [$message->content]);
            return;
        }
        extract($this->sql->single("SELECT MAX(`seq`) as `max_seq` FROM `mir4trades`;")) or throw new Error("failed to get max seq");
        $new_listings = [];
        foreach ($data['data']['lists'] as $listing) {
            if ($listing['seq'] <= $max_seq) continue;
            $new_listings[] = $listing;
        }
        foreach (array_reverse($new_listings) as $listing) {
            $this->log->debug("NewListingsConsumer received new listing", [$listing]);
            $listing = $this->sql->escape($listing);
            extract($listing);
            $query = "INSERT INTO `mir4trades` (
                    `seq`, `transportID`, `nftID`, `sealedDT`, `characterName`,
                    `class`, `lv`, `powerScore`, `price`,
                    `MirageScore`, `MiraX`, `Reinforce`
                ) VALUES (
                    '$seq', '$transportID', '$nftID', '$sealedDT', '$characterName',
                    '$class', '$lv', '$powerScore', '$price',
                    '$MirageScore', '$MiraX', '$Reinforce'
            );";
            $this->log->debug("NewListingsConsumer inserting new listing", [$query]);
            $this->sql->query($query);
            foreach ($this->stat_checks as $stat_check) {
                $this->publish('stat_checker', [
                    'seq' => $seq,
                    'transportID' => $transportID,
                    'stat_check' => $stat_check
                ]);
            }
        }
    }

    private function validateData($data): bool
    {
        return !is_null($data) && is_array($data) && isset($data['data']['lists']) && is_array($data['data']['lists']);
    }
}
