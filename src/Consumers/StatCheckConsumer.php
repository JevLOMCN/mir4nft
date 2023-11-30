<?php

namespace RPurinton\Mir4nft\Consumers;

use Bunny\Message;
use React\EventLoop\{LoopInterface};
use RPurinton\Mir4nft\{Log, MySQL, HTTPS, Error};
use RPurinton\Mir4nft\RabbitMQ\{Consumer};

class StatCheckConsumer
{
    public function __construct(private Log $log, private MySQL $sql, private LoopInterface $loop, private Consumer $mq)
    {
    }

    public function init(): bool
    {
        $this->sql->connect() or throw new Error("failed to connect to MySQL");
        $this->mq->connect($this->loop, "stat_checker", [$this, 'stats_callback']) or throw new Error("failed to connect to stat_check queue");
        return true;
    }

    public function stats_callback(Message $message): bool
    {
        $this->log->debug("received stats callback", [$message->content]);
        $data = json_decode($message->content, true);
        $this->validate_stats_callback($data) or throw new Error("received invalid stats callback");
        $this->insert_stats($data) or throw new Error("failed to insert stats");
        return true;
    }

    private function validate_stats_callback($data): bool
    {
        return is_array($data) && isset($data['seq']) && isset($data['transportID']) && isset($data['stat_check']) && isset($data['stat_url']);
    }

    private function insert_stats($data): bool
    {
        extract($data) or throw new Error("failed to extract data");
        $response = HTTPS::get($stat_url) or throw new Error("failed to get url");
        $response = $this->sql->escape($response) or throw new Error("failed to escape response");
        $query = "INSERT INTO `$stat_check` (
                `transportID`, `json`
            ) VALUES (
                '$transportID', '$response'
            ) ON DUPLICATE KEY UPDATE `json` = '$response';";
        $this->sql->query($query) or throw new Error("failed to insert stat");
        $this->log->debug("inserted stats", [$query]);
        return true;
    }
}
