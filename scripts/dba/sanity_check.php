<?php
$stat_checks = [
	"inven", "skills", "stats", "spirit",
	"magicorb", "magicstone", "mysticalpiece", "building",
	"training", "holystuff", "assets", "potential", "codex",
];
$sql = mysqli_connect("127.0.0.1", "mir4nft", "mir4nft", "mir4nft");
$result = $sql->query("SELECT * FROM `sequence`");
$count = $result->num_rows;
$counter = 0;
$checks_passed = 0;
while ($row = $result->fetch_assoc()) {
	$counter++;
	$seq = $row['seq'];
	$transportID = $row['transportID'];
	echo ("\rRow $counter of $count - $seq...");
	foreach ($stat_checks as $stat_check) {
		$stat = $sql->query("SELECT `json` FROM `$stat_check` WHERE `transportID` = '$transportID'");
		if ($stat->num_rows == 0) die("\n$stat_check not found for $transportID");
		$stat = $stat->fetch_assoc()['json'];
		$stat = json_decode($stat, true);
		if (!$stat) die("\n$stat_check not valid json for $transportID");
		if (!isset($stat['code'])) die("\n$stat_check missing code for $transportID");
		if (!isset($stat['data'])) die("\n$stat_check missing data for $transportID");
		if ($stat['code'] != 200) die("\n$stat_check code not 200 for $transportID");
		$checks_passed++;
	}
}
echo ("\ndone $counter\n");
echo ("checks passed $checks_passed\n");
