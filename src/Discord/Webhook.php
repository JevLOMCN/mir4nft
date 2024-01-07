<?php

namespace RPurinton\Mir4nft\Discord;

use RPurinton\Mir4nft\{
    HTTPS,
    Error,
};

class Webhook
{
    private string $url;

    public function __construct(array $message)
    {
        $this->url = getenv('DISCORD_WEBHOOK') or throw new Error("failed to getenv(DISCORD_WEBHOOK)");
        HTTPS::post($this->url, ['Content-Type: application/json'], json_encode($message));
    }
}
