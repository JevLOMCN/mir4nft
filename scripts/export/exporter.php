<?php

namespace RPurinton\Mir4nft;

ini_set('memory_limit', '-1');

require_once(__DIR__ . "/../../Composer.php");

$log = LogFactory::create("single");
$sql = new MySQL($log);

$data_file = __DIR__ . "/data.jsonl";
@unlink($data_file);

$result = $sql->query("SELECT `sequence`.`usd_price`,
        `summary`.`json` AS `summary`,
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
    WHERE `sequence`.`usd_price` IS NOT NULL
    ORDER BY `sequence`.`seq` ASC");
$count = $result->num_rows;
$counter = 0;
while ($row = $result->fetch_assoc()) {
    $counter++;
    echo "\r$counter of $count...";
    $record = Export::row($row);
    $usd_price = round($row['usd_price']);
    $messages = [
        "prompt" => json_encode($record) . "\n",
        "completion" => json_encode(["usd_price" => $usd_price]) . "\n"
    ];
    file_put_contents("data.jsonl", json_encode($messages) . "\n", FILE_APPEND);
}
echo "\n";
