#!/usr/bin/env php
<?php

namespace RPurinton\Mir4nft;

use RPurinton\Mir4nft\Consumers\NewListingsConsumer;

$worker_id = $argv[1] ?? 0;

try {
    require_once __DIR__ . "/../Composer.php";
    $log = LogFactory::create("new_listings-$worker_id") or throw new Error("failed to create log");
} catch (\Exception $e) {
    echo ("Fatal Exception " . $e->getMessage() . "\n");
    exit(1);
} catch (\Throwable $e) {
    echo ("Fatal Throwable " . $e->getMessage() . "\n");
    exit(1);
} catch (\Error $e) {
    echo ("Fatal Error " . $e->getMessage() . "\n");
    exit(1);
}

try {
    $me = new NewListingsConsumer($log, new MySQL($log), new RabbitMQ()) or throw new Error("failed to create NewListingsConsumer");
    $me->run() or throw new Error("failed to run NewListingsConsumer");
} catch (\Exception $e) {
    $log->debug("Fatal Exception " . $e->getMessage(), ["trace" => $e->getTrace()]);
    $log->error("Fatal Exception " . $e->getMessage());
} catch (\Throwable $e) {
    $log->debug("Fatal Throwable " . $e->getMessage(), ["trace" => $e->getTrace()]);
    $log->error("Fatal Throwable " . $e->getMessage());
} catch (\Error $e) {
    $log->debug("Fatal Error " . $e->getMessage(), ["trace" => $e->getTrace()]);
    $log->error("Fatal Error " . $e->getMessage());
}
