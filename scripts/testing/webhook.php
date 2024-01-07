<?php

namespace RPurinton\Mir4nft\Discord;

require_once(__DIR__ . '/../../Composer.php');


$embed = [
    'title' => 'Title of the Embed',
    'description' => 'Description of the Embed',
    'url' => 'https://example.com',
    'timestamp' => date('Y-m-d\TH:i:s.vZ'),
    'color' => hexdec('ff0000'),
    'footer' => [
        'text' => 'Footer Text',
        'icon_url' => 'https://example.com/footer-icon.png'
    ],
    'image' => [
        'url' => 'https://example.com/image.png'
    ],
    'thumbnail' => [
        'url' => 'https://example.com/thumbnail.png'
    ],
    'video' => [
        'url' => 'https://example.com/video.mp4'
    ],
    'provider' => [
        'name' => 'Provider Name',
        'url' => 'https://example.com/provider'
    ],
    'author' => [
        'name' => 'Author Name',
        'url' => 'https://example.com/author',
        'icon_url' => 'https://example.com/author-icon.png'
    ],
    'fields' => [
        [
            'name' => 'Field 1',
            'value' => 'Value 1',
            'inline' => false
        ],
        [
            'name' => 'Field 2',
            'value' => 'Value 2',
            'inline' => true
        ]
    ]
];

new Webhook(['embeds' => [$embed]]);
