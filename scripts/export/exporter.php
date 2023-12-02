<?php

// Database connection
$db = mysqli_connect("127.0.0.1", "mir4nft", "mir4nft", "mir4nft");

// Fetch all transportID and class from the transports table
$result = $db->query("SELECT
`sequence`.*,
`transports`.*,
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
FROM `sequence`,
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
ORDER BY `seq` ASC");
while ($row = $result->fetch_assoc()) {
    // dump everything to a single jsonl file
    $json = json_encode($row);
    file_put_contents("data.jsonl", $json . "\n", FILE_APPEND);
}
