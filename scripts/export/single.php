<?php

namespace RPurinton\Mir4nft;

use COM;
use RPurinton\Mir4nft\OpenAI\Client;

if (!isset($argv[1])) {
    echo "Usage: php single.php <seq>\n";
    exit;
}

require_once(__DIR__ . '/../../Composer.php');
$log = LogFactory::create("single");
$ai = new Client($log);

$seq = $argv[1];

// Database connection
$db = mysqli_connect("127.0.0.1", "mir4nft", "mir4nft", "mir4nft");

// Fetch all transportID and class from the db
$result = $db->query("SELECT `sequence`.`usd_price`,
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
    WHERE `sequence`.`seq` = $seq");
<<<<<<< HEAD
while ($row = $result->fetch_assoc()) {
    $record = [];

    // summary
    $summary = json_decode($row['summary'], true)['data'];
    // transport parts
    $record['class'] = getClass($summary['character']['class']);
    $record['level'] = $summary['character']['level'];
    $record['powerScore'] = $summary['character']['powerScore'];
    foreach ($summary['equipItem'] as $equipItem) $record['equipItems'][] = [
        'name' => $equipItem['itemName'],
        'grade' => getGrade($equipItem['grade']),
        'tier' => $equipItem['tier'],
        'enhance' => $equipItem['enhance'],
    ];

    // assets
    $record['assets'] = json_decode($row['assets'], true)['data'];

    // building
    $buildings = json_decode($row['building'], true)['data'];
    foreach ($buildings as $building) {
        $buildingName = $building['buildingName'];
        $record['conquests'][$buildingName] = $building['buildingLevel'];
    }

    // codex
    $codex = json_decode($row['codex'], true)['data'];
    foreach ($codex as $codexItem) {
        $codexName = $codexItem['codexName'];
        unset($codexItem['codexName']);
        $record['codex'][$codexName] = $codexItem;
    }

    // holystuff
    $holystuff = json_decode($row['holystuff'], true)['data'];
    foreach ($holystuff as $holystuffItem) {
        $holystuffName = $holystuffItem['HolyStuffName'];
        $record['holystuff'][$holystuffName] = $holystuffItem['Grade'];
    }

    // magicorb
    $magicorb = json_decode($row['magicorb'], true)['data']['equipItem'];
    foreach ($magicorb as $deck) foreach ($deck as $item) $record['magicorbs'][$item['itemName']] = [
        'grade' => getGrade($item['grade']),
        'level' => $item['itemLv'],
        'exp' => $item['itemExp'],
        'tier' => $item['tier'],
    ];

    // mysticalpiece
    $mysticalpiece = json_decode($row['mysticalpiece'], true)['data']['equipItem'];
    foreach ($mysticalpiece as $deck) foreach ($deck as $item) {
        if ($item['grade'] >= 4) $record['mysticalpieces'][$item['itemName']] = [
            'grade' => getGrade($item['grade']),
            'tier' => $item['tier']
        ];
    }

    // potential
    $record['potential'] = json_decode($row['potential'], true)['data'];

    // skills
    $skills = json_decode($row['skills'], true)['data'];
    foreach ($skills as $skill) {
        $record['skills'][$skill['skillName']] = $skill['skillLevel'];
    }

    // spirit
    $spirit = json_decode($row['spirit'], true)['data']['inven'];
    foreach ($spirit as $spiritItem) {
        if ($spiritItem['grade'] >= 4) $record['spirits'][] = [
            'name' => $spiritItem['petName'],
            'grade' => getGrade($spiritItem['grade']),
        ];
    }

    // stats
    $stats = json_decode($row['stats'], true)['data']['lists'];
    $stats_wanted = ["HP", "MP", "PHYS ATK", "PHYS DEF", "Spell ATK", "Spell DEF", "Accuracy", "EVA", "CRIT", "CRIT EVA"];
    foreach ($stats as $stat) if (in_array($stat['statName'], $stats_wanted)) $record['stats'][$stat['statName']] = $stat['statValue'];


    // training
    $training = json_decode($row['training'], true)['data'];
    $record['training']['Constitution'] = $training['consitutionLevel'] ?? "Unknown";
    $record['training']['Solitude'] = $training['collectLevel'] ?? "Unknown";
    unset($training['consitutionLevel']);
    unset($training['collectLevel']);
    unset($training['consitutionName']);
    unset($training['collectName']);
    foreach ($training as $trainingItem) $record['training'][$trainingItem['forceName']] = $trainingItem['forceLevel'];

    echo (json_encode($record) . "\n");
=======
if (!$result) {
    echo "Error: " . $db->error . "\n";
    exit;
>>>>>>> dcdb0e5eafb3ade9cee945fb13c507e91ea2817e
}
$row = $result->fetch_assoc();
$record = [];

// summary
$summary = json_decode($row['summary'], true)['data'];
// transport parts
$record['class'] = getClass($summary['character']['class']);
$record['level'] = $summary['character']['level'];
$record['powerScore'] = $summary['character']['powerScore'];
foreach ($summary['equipItem'] as $equipItem) $record['equipItems'][] = [
    'name' => $equipItem['itemName'],
    'grade' => getGrade($equipItem['grade']),
    'tier' => $equipItem['tier'],
    'enhance' => $equipItem['enhance'],
];

// assets
$record['assets'] = json_decode($row['assets'], true)['data'];

// building
$buildings = json_decode($row['building'], true)['data'];
foreach ($buildings as $building) {
    $buildingName = $building['buildingName'];
    $record['conquests'][$buildingName] = $building['buildingLevel'];
}

// codex
$codex = json_decode($row['codex'], true)['data'];
foreach ($codex as $codexItem) {
    $codexName = $codexItem['codexName'];
    unset($codexItem['codexName']);
    $record['codex'][$codexName] = $codexItem;
}

// holystuff
$holystuff = json_decode($row['holystuff'], true)['data'];
foreach ($holystuff as $holystuffItem) {
    $holystuffName = $holystuffItem['HolyStuffName'];
    $record['holystuff'][$holystuffName] = $holystuffItem['Grade'];
}

// magicorb
$magicorb = json_decode($row['magicorb'], true)['data']['equipItem'];
foreach ($magicorb as $deck) foreach ($deck as $item) $record['magicorbs'][$item['itemName']] = [
    'grade' => getGrade($item['grade']),
    'level' => $item['itemLv'],
    'exp' => $item['itemExp'],
    'tier' => $item['tier'],
];

// mysticalpiece
$mysticalpiece = json_decode($row['mysticalpiece'], true)['data']['equipItem'];
foreach ($mysticalpiece as $deck) foreach ($deck as $item) {
    if ($item['grade'] >= 4) $record['mysticalpieces'][$item['itemName']] = [
        'grade' => getGrade($item['grade']),
        'tier' => $item['tier']
    ];
}

// potential
$record['potential'] = json_decode($row['potential'], true)['data'];

// skills
$skills = json_decode($row['skills'], true)['data'];
foreach ($skills as $skill) {
    $record['skills'][$skill['skillName']] = $skill['skillLevel'];
}

// spirit
$spirit = json_decode($row['spirit'], true)['data']['inven'];
foreach ($spirit as $spiritItem) {
    if ($spiritItem['grade'] >= 4) $record['spirits'][] = [
        'name' => $spiritItem['petName'],
        'grade' => getGrade($spiritItem['grade']),
        'transend' => $spiritItem['transcend']
    ];
}

// stats
$stats = json_decode($row['stats'], true)['data']['lists'];
$stats_wanted = ["HP", "MP", "PHYS ATK", "PHYS DEF", "Spell ATK", "Spell DEF", "Accuracy", "EVA", "CRIT", "CRIT EVA"];
foreach ($stats as $stat) if (in_array($stat['statName'], $stats_wanted)) $record['stats'][$stat['statName']] = $stat['statValue'];


// training
$training = json_decode($row['training'], true)['data'];
$record['training']['Constitution'] = $training['consitutionLevel'] ?? "Unknown";
$record['training']['Solitude'] = $training['collectLevel'] ?? "Unknown";
unset($training['consitutionLevel']);
unset($training['collectLevel']);
unset($training['consitutionName']);
unset($training['collectName']);
foreach ($training as $trainingItem) $record['training'][$trainingItem['forceName']] = $trainingItem['forceLevel'];

$prompt = json_encode($record);
$log->info("prompt", ['prompt' => $prompt]);
$result = $ai->complete($prompt);
$log->info("result", ['result' => $result]);

function getGrade($grade)
{
    $grade = strval($grade);
    return match ($grade) {
        "1" => "1",
        "2" => "2",
        "3" => "3",
        "4" => "4",
        "5" => "5",
        default => "1"
    };
}

function getClass($class)
{
    $class = strval($class);
    return match ($class) {
        "1" => "Warrior",
        "2" => "Sorcerer",
        "3" => "Taoist",
        "4" => "Arbalist",
        "5" => "Lancer",
        "6" => "Darkist",
        default => "Warrior"
    };
}

function tradeable($itemID)
{
    return match (substr($itemID, 3, 1)) {
        "1" => "yes",
        default => "no"
    };
}
