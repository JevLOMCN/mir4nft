<?php

$sql = mysqli_connect("127.0.0.1", "mir4nft", "mir4nft", "mir4nft");
$result = $sql->query("SELECT * FROM `sequence`");
$count = $result->num_rows;
$counter = 0;
while ($row = $result->fetch_assoc()) {
	$counter++;
	$seq = $row['seq'];
	$transportID = $row['transportID'];
	echo ("\rRow $counter of $count - $seq...");
	$transport_check = $sql->query("SELECT * FROM `transport` WHERE `transportID` = '$transportID'");
	if ($transport_check->num_rows == 0) die("\nTransport $seq - $transportID not found");
}
echo ("\ndone $counter\n");
