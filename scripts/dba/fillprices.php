<?php

namespace RPurinton\Mir4nft;

require_once(__DIR__ . '/../../Composer.php');

use OpenAI;
use RPurinton\Mir4nft\{
    LogFactory,
    MySQL,
    OpenAI\Client,
    RabbitMQ\Publisher
};

$log = LogFactory::create('fillprices');
$sql = new MySQL($log);
$ai = new Client($log);
$pub = new Publisher();
$query = "SELECT sq.transportID, MAX(sq.seq) AS seq
FROM `sequence` sq
LEFT JOIN `evals` ev ON sq.transportID = ev.transportID
WHERE ev.transportID IS NULL
GROUP BY sq.transportID;";
$result = $sql->query($query);
$total = $result->num_rows;
$counter = 0;
while ($row = $result->fetch_assoc()) {
    $counter++;
    $seq = $row['seq'];
    $transportID = $row['transportID'];
    echo ("\rProcessing $seq $transportID - $counter of $total...");
}
echo ("done!\n");
