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

$query = "SELECT `transportID`
FROM `transports`
WHERE `transportID` NOT IN (
  SELECT DISTINCT `transportID`
  FROM `evals`
)";
$result = $sql->query($query);
$total = $result->num_rows;
$counter = 0;
while ($row = $result->fetch_assoc()) {
    $counter++;
    $transportID = $row['transportID'];
    echo ("\rProcessing $transportID - $counter of $total...");
}
echo ("done!\n");
