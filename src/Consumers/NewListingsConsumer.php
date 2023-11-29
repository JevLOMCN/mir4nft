<?php

namespace RPurinton\Mir4nft\Consumers;

use React\Async;
use RPurinton\Mir4nft\{RabbitMQ, Log, MySQL};
use Bunny\{Async\Client, Channel, Message};

class NewListingsConsumer extends RabbitMQ
{
    public function __construct(private Log $log, private MySQL $sql)
    {
        parent::__construct('new_listings');
        $this->log->debug("NewListingsConsumer initialized!");
    }

    public function process(Message $message, Channel $channel, Client $client)
    {
        $data = json_decode($message->content, true);
        $this->log->debug("NewListingsConsumer received message", $data);
    }
}
