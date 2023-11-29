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

    public function process(Message $message, Channel $channel, Client $client): void
    {
        $data = json_decode($message->content, true);
        if ($data) {
            $this->log->debug("NewListingsConsumer received message", $data);
            // TODO: validate the message
            // TODO: get the current max sequence number from the database
            // TODO: iterate through the listings and insert any new ones
            // TODO: for each new listing publish messages to the stat checker queue
        } else {
            $this->log->error("NewListingsConsumer received invalid message", [$message->content]);
        }
    }
}
