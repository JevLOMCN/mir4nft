<?php

namespace RPurinton\Mir4nft\Discord;

use Discord\Discord;
use Discord\Parts\Channel\Message;

class Client
{
    public Discord $discord;

    public function __construct()
    {
        $this->discord = new Discord([
            'token' => config('discord.token'),
        ]);
    }

    public function sendMessage(string $message): Message
    {
        return $this->discord->channel->createMessage([
            'channel.id' => config('discord.channel_id'),
            'content' => $message,
        ]);
    }
}
