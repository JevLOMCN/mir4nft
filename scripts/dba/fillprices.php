<?php

namespace RPurinton\Mir4nft;

require_once(__DIR__ . '/../../Composer.php');

use OpenAI;
use RPurinton\Mir4nft\{
    LogFactory,
    MySQL,
    OpenAI\Client
};

$log = LogFactory::create('fillprices');
$sql = new MySQL($log);
$ai = new Client($log);

$log->info("Starting fillprices.php");
