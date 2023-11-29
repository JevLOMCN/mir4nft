<?php

namespace RPurinton\Mir4nft\Consumers;

use React\Async;
use RPurinton\Mir4nft\{RabbitMQ, Log, MySQL, Error};
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
        $data = json_decode($message->content, true) or throw new Error('Failed to decode the message');
        $this->log->debug("NewListingsConsumer received message", $data);
        Async\await($channel->ack($message)) or throw new Error('Failed to ack the message');
    }
}
