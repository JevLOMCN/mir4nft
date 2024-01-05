<?php

namespace RPurinton\Mir4nft;

use COM;
use RPurinton\Mir4nft\OpenAI\Client;


require_once(__DIR__ . '/../../Composer.php');
$log = LogFactory::create("single");
$sql = new MySQL($log);
$ai = new Client($log);


if (!isset($argv[1])) {
    echo "Usage: php single.php <seq>\n";
    exit;
}

$seq = $argv[1];

// Fetch all transportID and class from the db
$result = $sql->query("SELECT `summary`.`json` AS `summary`,
        `assets`.`json` AS `assets`,
        `building`.`json` AS `building`,
        `codex`.`json` AS `codex`,
        `holystuff`.`json` AS `holystuff`,
        `magicorb`.`json` AS `magicorb`,
        `mysticalpiece`.`json` AS `mysticalpiece`,
        `potential`.`json` AS `potential`,
        `skills`.`json` AS `skills`,
        `spirit`.`json` AS `spirit`,
        `stats`.`json` AS `stats`,
        `training`.`json` AS `training`
    FROM `sequence`
    INNER JOIN `summary` ON `sequence`.`seq` = `summary`.`seq`
    INNER JOIN `assets` ON `sequence`.`transportID` = `assets`.`transportID`
    INNER JOIN `building` ON `sequence`.`transportID` = `building`.`transportID`
    INNER JOIN `codex` ON `sequence`.`transportID` = `codex`.`transportID`
    INNER JOIN `holystuff` ON `sequence`.`transportID` = `holystuff`.`transportID`
    INNER JOIN `magicorb` ON `sequence`.`transportID` = `magicorb`.`transportID`
    INNER JOIN `mysticalpiece` ON `sequence`.`transportID` = `mysticalpiece`.`transportID`
    INNER JOIN `potential` ON `sequence`.`transportID` = `potential`.`transportID`
    INNER JOIN `skills` ON `sequence`.`transportID` = `skills`.`transportID`
    INNER JOIN `spirit` ON `sequence`.`transportID` = `spirit`.`transportID`
    INNER JOIN `stats` ON `sequence`.`transportID` = `stats`.`transportID`
    INNER JOIN `training` ON `sequence`.`transportID` = `training`.`transportID`
    WHERE `sequence`.`seq` = $seq");

if (!$result) {
    echo "Error: " . $db->error . "\n";
    exit;
}

$record = Export::row($result->fetch_assoc());
echo (json_encode($record, JSON_PRETTY_PRINT) . "\n");
$usd_price = json_decode($ai->complete(json_encode($record)), true)['usd_price'];
echo "USD Price: \$$usd_price\n";
