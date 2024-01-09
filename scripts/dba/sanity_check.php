<?php

$sql = mysqli_connect("127.0.0.1","mir4nft","mir4nft","mir4nft");
$result = $sql->query("SELECT * FROM `sequence`");
$count = $result->num_rows;
$counter = 0;
while($row = $result->fetch_assoc())
{
	$counter++;
	$seq = $row['seq'];
	echo("\rRow $counter of $count - $seq...");
}
echo("\ndone $counter\n");

