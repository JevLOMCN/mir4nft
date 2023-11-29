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
            //$seq = $this->sql->insert("INSERT INTO listings (listing_id, item_id, item_name, item_type, item_grade, item_level, item_price, item_quantity, item_seller, item_server, item_time) VALUES ({$data['listing_id']}, {$data['item_id']}, '{$data['item_name']}', '{$data['item_type']}', {$data['item_grade']}, {$data['item_level']}, {$data['item_price']}, {$data['item_quantity']}, '{$data['item_seller']}', '{$data['item_server']}', {$data['item_time']})");
        } else {
            $this->log->error("NewListingsConsumer received invalid message", [$message->content]);
        }
    }
}
