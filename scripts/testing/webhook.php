<?php

namespace RPurinton\Mir4nft\Discord;

require_once(__DIR__ . '/../../Composer.php');


$embed = [
    'title' => '120 Taoist 231,203',
    'description' => 'GREAT DEAL!',
    'url' => 'https://xdraco.com',
    'timestamp' => date('c'),
    'color' => hexdec('00ff00'),
    'fields' => [
        [
            'name' => '**__Ask__**',
            'value' => "W100\n$300",
            'inline' => true
        ],
        [
            'name' => '**__Value__**',
            'value' => "W200\n$600",
            'inline' => true
        ],
        [
            'name' => '**__Diff__**',
            'value' => "100%^\n$300",
            'inline' => true
        ]
    ]
];

new Webhook([
    'content' => '<t:' . time() . ':R>',
    'embeds' => [$embed]
]);
