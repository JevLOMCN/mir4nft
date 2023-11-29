<?php

namespace RPurinton\Mir4nft;

class Log extends \Monolog\Logger
{
    public function __construct(private string $myName)
    {
        parent::__construct($myName);
        $this->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Level::Debug));
    }
}
