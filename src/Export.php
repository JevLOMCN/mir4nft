<?php

namespace RPurinton\Mir4nft;

class Export
{
    const KEYS = [
        'summary', 'assets', 'building', 'codex',
        'holystuff', 'magicorb', 'mysticalpiece',
        'potential', 'skills', 'spirit', 'stats', 'training'
    ];

    const STATS = [
        'HP', 'MP',
        'PHYS ATK', 'PHYS DEF',
        'Spell ATK', 'Spell DEF',
        'Accuracy', 'EVA',
        'CRIT', 'CRIT EVA',
        'CRIT ATK DMG Boost', 'CRIT DMG Reduction',
        'Bash ATK DMG Boost', 'Bash DMG Reduction',
        'PvP ATK DMG Boost', 'PvP DMG Reduction',
        'Monster ATK DMG Boost', 'Monster DMG Reduction',
        'Boss ATK DMG Boost', 'Boss DMG Reduction',
        'Skill ATK DMG Boost', 'Skill DMG Reduction',
        'Basic ATK DMG Boost', 'Basic DMG Reduction',
        'All ATK DMG Boost', 'All DMG Reduction',
        'Stun Success Boost', 'Stun RES Boost',
        'Debilitation Success Boost', 'Debilitation RES Boost',
        'Silence Success Boost', 'Silence RES Boost',
        'Knockdown Success Boost', 'Knockdown RES Boost',
        'Drop Change Boost', 'Lucky Drop Chance Boost',
        'Hunting EXP Boost', 'Hunting Copper Gain Boost',
        'Antidemon Power', 'Gathering Boost',
        'Energy Gathering Boost', 'Energy Gain Boost',
        'Mining Boost', 'Darksteel Gain Boost',
        'Skill Cooldown Reduction',
    ];

    /**
     * Processes a row of data.
     *
     * This method iterates over the keys defined in the KEYS constant. For each key, it checks if the key exists in the input row.
     * If the key does not exist, it throws an error. If the key exists, it checks if a method with the same name as the key exists in the class.
     * If the method does not exist, it throws an error. If the method exists, it calls the method with the decoded data from the row as the argument.
     * The result of the method call is stored in the record array with the key as the array key.
     *
     * @param array $row The row of data to process.
     * @return array The processed row of data.
     * @throws Error If a key is missing from the row or if an invalid key is provided.
     */
    public static function row(array $row): array
    {
        $record = [];

        foreach (self::KEYS as $key) {
            if (!array_key_exists($key, $row)) {
                throw new Error('Missing key: ' . $key);
            }

            if (!method_exists(self::class, $key)) {
                throw new Error('Invalid key provided: ' . $key);
            }

            $decodedData = self::decode($row[$key]);
            $record[$key] = self::$key($decodedData);
        }

        return $record;
    }

    /**
     * Decodes a JSON string into an array.
     *
     * This method takes a JSON string as input and attempts to decode it into an array. If the decoding is successful, it checks if the decoded data is an array and if it contains a 'data' key.
     * If the decoded data is not an array or does not contain a 'data' key, it throws an error. If the decoding is not successful, it throws an error with the last JSON error message.
     * If the decoding is successful and the decoded data is an array with a 'data' key, it sorts the 'data' array by its keys and returns it.
     *
     * @param string $json The JSON string to decode.
     * @return array|null The decoded data array or null if the input is not a valid JSON string.
     * @throws Error If the decoded data is not an array, does not contain a 'data' key, or if a JSON error occurred.
     */
    public static function decode(string $json): ?array
    {
        $decodedJson = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Error('Invalid JSON provided: ' . json_last_error_msg());
        }

        if (!is_array($decodedJson) || !array_key_exists('data', $decodedJson)) {
            throw new Error('Invalid JSON provided: Missing data key');
        }

        ksort($decodedJson['data']);

        return $decodedJson['data'];
    }

    /**
     * Extracts summary data from the given array and transforms it into a more usable format.
     *
     * @param array $summary The summary data to process.
     * @return array The processed summary data.
     */
    public static function summary(array $summary): array
    {
        $processedSummary = [
            'class' => $summary['character']['class'],
            'level' => $summary['character']['level'],
            'power' => $summary['character']['powerScore'],
            'equip' => self::summary_equip($summary['equipItem']),
        ];

        return $processedSummary;
    }

    /**
     * Processes equip items data.
     *
     * @param array $equipItems The equip items data to process.
     * @return array The processed equip items data.
     */
    private static function summary_equip(array $equipItems): array
    {
        $processedEquipItems = [];

        foreach ($equipItems as $equipItem) {
            $processedEquipItems[] = [
                'id' => $equipItem['itemIdx'],
                'type' => $equipItem['itemType'],
                'grade' => $equipItem['grade'],
                'tier' => $equipItem['tier'],
                'enhance' => $equipItem['enhance'],
                'tradeable' => substr($equipItem['itemIdx'], 3, 1) === '1' ? 1 : 0,
            ];
        }

        return $processedEquipItems;
    }

    /**
     * Processes the assets data.
     *
     * This method simply returns the input assets data as it is.
     *
     * @param array $assets The assets data to process.
     * @return array The processed assets data.
     */
    public static function assets(array $assets): array
    {
        return $assets;
    }

    /**
     * Processes the buildings data.
     *
     * This method iterates over the input buildings data and extracts the 'buildingLevel' from each building.
     * The extracted 'buildingLevel' values are stored in an array which is returned.
     *
     * @param array $buildings The buildings data to process.
     * @return array The processed buildings data.
     */
    public static function building(array $buildings): array
    {
        $processedBuildings = [];

        foreach ($buildings as $building) {
            $processedBuildings[] = $building['buildingLevel'];
        }

        return $processedBuildings;
    }

    /**
     * Processes the codex data.
     *
     * This method iterates over the input codex data and for each codex item, it removes the 'codexName' key and stores the rest of the codex item data in an array with the 'codexName' as the key.
     * The resulting array is returned.
     *
     * @param array $codex The codex data to process.
     * @return array The processed codex data.
     */
    public static function codex(array $codex): array
    {
        $processedCodex = [];

        foreach ($codex as $codexItem) {
            $codexName = $codexItem['codexName'];
            unset($codexItem['codexName']);
            $processedCodex[$codexName] = $codexItem;
        }

        return $processedCodex;
    }

    /**
     * Processes the holystuff data.
     *
     * This method iterates over the input holystuff data and for each holystuff item, it extracts the 'Grade' and stores it in an array.
     * The resulting array is returned.
     *
     * @param array $holystuff The holystuff data to process.
     * @return array The processed holystuff data.
     */
    public static function holystuff(array $holystuff): array
    {
        $processedHolystuff = [];

        foreach ($holystuff as $holystuffItem) {
            $processedHolystuff[] = $holystuffItem['Grade'];
        }

        return $processedHolystuff;
    }

    /**
     * Processes the magicorb data.
     *
     * This method iterates over the input magicorb data and for each magicorb item, it extracts the 'itemIdx', 'grade', 'itemLv', 'itemExp', and 'tier' and stores them in an array with 'itemIdx' as the key.
     * The resulting array is returned.
     *
     * @param array $magicorb The magicorb data to process.
     * @return array The processed magicorb data.
     */
    public static function magicorb(array $magicorb): array
    {
        $processedMagicorb = [];

        foreach ($magicorb['equipItem'] as $deck) {
            foreach ($deck as $item) {
                $processedMagicorb[$item['itemIdx']] = [
                    'grade' => $item['grade'],
                    'level' => $item['itemLv'],
                    'exp' => $item['itemExp'],
                    'tier' => $item['tier'],
                ];
            }
        }

        return $processedMagicorb;
    }

    /**
     * Processes the mysticalpiece data.
     *
     * This method iterates over the input mysticalpiece data and for each mysticalpiece item, if the 'grade' is greater than or equal to 4, it extracts the 'itemIdx', 'grade', and 'tier' and stores them in an array with 'itemIdx' as the key.
     * The resulting array is returned.
     *
     * @param array $mysticalpiece The mysticalpiece data to process.
     * @return array The processed mysticalpiece data.
     */
    public static function mysticalpiece(array $mysticalpiece): array
    {
        $processedMysticalpiece = [];

        foreach ($mysticalpiece['equipItem'] as $deck) {
            foreach ($deck as $item) {
                if ($item['grade'] >= 4) {
                    $processedMysticalpiece[$item['itemIdx']] = [
                        'grade' => $item['grade'],
                        'tier' => $item['tier']
                    ];
                }
            }
        }

        return $processedMysticalpiece;
    }

    /**
     * Processes the potential data.
     *
     * This method simply returns the input potential data as it is.
     *
     * @param array $potential The potential data to process.
     * @return array The processed potential data.
     */
    public static function potential(array $potential): array
    {
        return $potential;
    }
    /**
     * Processes the skills data.
     *
     * This method iterates over the input skills data and for each skill item, it extracts the 'skillName' and 'skillLevel' and stores them in an array with 'skillName' as the key.
     * The resulting array is sorted by 'skillName' in ascending order and returned.
     *
     * @param array $skills The skills data to process.
     * @return array The processed skills data.
     */
    public static function skills(array $skills): array
    {
        $processedSkills = [];

        foreach ($skills as $skill) {
            $processedSkills[$skill['skillName']] = $skill['skillLevel'];
        }

        ksort($processedSkills);

        return $processedSkills;
    }

    /**
     * Processes the spirit data.
     *
     * This method iterates over the input spirit data and for each spirit item, it extracts the 'spiritId' and 'spiritLv' and stores them in an array with 'spiritId' as the key.
     * The resulting array is sorted by 'spiritId' in ascending order and returned.
     *
     * @param array $spirit The spirit data to process.
     * @return array The processed spirit data.
     */
    public static function spirit(array $spirit): array
    {
        $processedSpirit = [];

        foreach ($spirit['inven'] as $spiritItem) {
            if ($spiritItem['grade'] >= 4) {
                $processedSpirit[] = [
                    'name' => $spiritItem['petName'],
                    'grade' => $spiritItem['grade']
                ];
            }
        }

        usort($processedSpirit, function ($a, $b) {
            return $a['grade'] - $b['grade'] ?: strcmp($a['name'], $b['name']);
        });

        return $processedSpirit;
    }

    /**
     * Processes the stats data.
     *
     * This method iterates over the STATS constant and for each stat in STATS, it checks if the stat exists in the input stats data.
     * If it does, it stores the 'statValue' in an array with the stat as the key.
     * The resulting array is returned.
     *
     * @param array $stats The stats data to process.
     * @return array The processed stats data.
     */
    public static function stats(array $stats): array
    {
        $processedStats = [];
        $statsData = array_column($stats, 'statValue', 'statId');

        foreach (self::STATS as $stat) {
            if (isset($statsData[$stat])) {
                $processedStats[$stat] = $statsData[$stat];
            }
        }

        return $processedStats;
    }

    /**
     * Processes the training data.
     *
     * This method iterates over the input training data and for each training item, it extracts the 'trainingId' and 'trainingValue' and stores them in an array with 'trainingId' as the key.
     * The resulting array is sorted by 'trainingId' in ascending order and returned.
     *
     * @param array $training The training data to process.
     * @return array The processed training data.
     */
    public static function training(array $training): array
    {
        $processedTraining = [
            'Constitution' => $training['consitutionLevel'] ?? 'Unknown',
            'Solitude' => $training['collectLevel'] ?? 'Unknown',
        ];

        foreach (['consitutionLevel', 'collectLevel', 'consitutionName', 'collectName'] as $key) {
            unset($training[$key]);
        }

        foreach ($training as $trainingItem) {
            $processedTraining[$trainingItem['trainingId']] = $trainingItem['trainingValue'];
        }

        ksort($processedTraining);

        return $processedTraining;
    }
}
