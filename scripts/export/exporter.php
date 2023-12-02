<?php

// Database connection
$db = mysqli_connect("127.0.0.1", "mir4nft", "mir4nft", "mir4nft");

// Fetch all transportID and class from the transports table
$result = $db->query("SELECT
`sequence`.`usd_price`,
`transports`.`class`,
`transports`.`lv`,
`transports`.`powerScore`,
`summary`.`json` AS `summary`,
`assets`.`json` AS `assets`,
`building`.`json` AS `building`,
`codex`.`json` AS `codex`,
`holystuff`.`json` AS `holystuff`,
`inven`.`json` AS `inven`,
`magicorb`.`json` AS `magicorb`,
`magicstone`.`json` AS `magicstone`,
`mysticalpiece`.`json` AS `mysticalpiece`,
`potential`.`json` AS `potential`,
`skills`.`json` AS `skills`,
`stats`.`json` AS `stats`,
`training`.`json` AS `training`
FROM `sequence`
INNER JOIN `transports` ON `sequence`.`transportID` = `transports`.`transportID`
INNER JOIN `summary` ON `sequence`.`seq` = `summary`.`seq`
INNER JOIN `assets` ON `sequence`.`transportID` = `assets`.`transportID`
INNER JOIN `building` ON `sequence`.`transportID` = `building`.`transportID`
INNER JOIN `codex` ON `sequence`.`transportID` = `codex`.`transportID`
INNER JOIN `holystuff` ON `sequence`.`transportID` = `holystuff`.`transportID`
INNER JOIN `inven` ON `sequence`.`transportID` = `inven`.`transportID`
INNER JOIN `magicorb` ON `sequence`.`transportID` = `magicorb`.`transportID`
INNER JOIN `magicstone` ON `sequence`.`transportID` = `magicstone`.`transportID`
INNER JOIN `mysticalpiece` ON `sequence`.`transportID` = `mysticalpiece`.`transportID`
INNER JOIN `potential` ON `sequence`.`transportID` = `potential`.`transportID`
INNER JOIN `skills` ON `sequence`.`transportID` = `skills`.`transportID`
INNER JOIN `stats` ON `sequence`.`transportID` = `stats`.`transportID`
INNER JOIN `training` ON `sequence`.`transportID` = `training`.`transportID`
WHERE `sequence`.`usd_price` IS NOT NULL
ORDER BY `sequence`.`seq` ASC");
while ($row = $result->fetch_assoc()) {
    $usd_price = "$" . number_format($row['usd_price'], 2, ".", ",");
    $record = [];

    // transport parts
    $record['class'] = match ($row['class']) {
        1 => "Warrior",
        2 => "Sorcerer",
        3 => "Taoist",
        4 => "Arbalist",
        5 => "Lancer",
        6 => "Darkist",
        default => "Warrior"
    };
    $record['lv'] = $row['lv'];
    $record['powerScore'] = $row['powerScore'];

    // summary
    $summary = json_decode($row['summary'], true);
    foreach ($summary['data']['equipItem'] as $equipItem) {
        $record['equipItem'][] = [
            'name' => $equipItem['itemName'],
            'grade' => getGrade($equipItem['grade']),
            'tier' => $equipItem['tier'],
            'enhance' => $equipItem['enhance'],
            'refineStep' => $equipItem['refineStep'],
        ];
    }
    $messages[] = ["role" => "system", "content" => "You are the Mir4info NFT Valuation Tool."];
    $messages[] = ["role" => "user", "content" => json_encode($record)];
    $messages[] = ["role" => "assistant", "content" => "Sale Price $usd_price"];
    $messages2["messages"] = $messages;
    $json_string = json_encode($messages2);
    file_put_contents("data.jsonl", $json_string . "\n", FILE_APPEND);
}

function getGrade($grade)
{
    return match ($grade) {
        1 => "Common",
        2 => "Uncommon",
        3 => "Rare",
        4 => "Epic",
        5 => "Legendary",
        default => "Common"
    };
}
