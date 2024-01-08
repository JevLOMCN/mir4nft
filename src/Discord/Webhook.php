<?php

namespace RPurinton\Mir4nft\Discord;

use RPurinton\Mir4nft\{
    Mir4\ClassName,
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

    public static function embed(
        int $lv,
        int $class,
        int $power,
        string $dealtext,
        int $seq,
        string $color,
        int $ask_wemix,
        int $ask_usd,
        int $value_wemix,
        int $value_usd,
        int $diff_pct,
        int $diff_usd
    ): array {
        $class = ClassName::get($class);
        $power = number_format($power, 0, '.', ',');
        return [
            'title' => "$lv $class $power",
            'description' => "$dealtext\n<t:" . time() . ':R>',
            'url' => 'https://xdraco.com/nft/trade/' . $seq,
            'color' => hexdec($color),
            'fields' => [
                [
                    'name' => '**__Ask__**',
                    'value' => "W$ask_wemix\n$" . $ask_usd,
                    'inline' => true
                ],
                [
                    'name' => '**__Value__**',
                    'value' => "W$value_wemix\n$" . $value_usd,
                    'inline' => true
                ],
                [
                    'name' => '**__Diff__**',
                    'value' => "$diff_pct%\n$" . $diff_usd,
                    'inline' => true
                ]
            ]
        ];
    }
}
