<?php

namespace RPurinton\Mir4nft;

class HTTPS
{
    static function get($url): string
    {
        return file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) " .
                    "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36\r\n"
            ]
        ])) or throw new Error("failed to get contents");
    }

    static function post($url, $data): string
    {
        return file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) " .
                    "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36\r\n" .
                    "Content-Type: application/json\r\n",
                'content' => json_encode($data)
            ]
        ])) or throw new Error("failed to get contents");
    }
}
