<?php

namespace RPurinton\Mir4nft;

class HTTPS
{
    static function get($url): string
    {
        $response = file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) " .
                    "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36\r\n"
            ]
        ])) or throw new Error("failed to get contents");
        return $response;
    }
}
