<?php

// Database connection
$db = mysqli_connect("127.0.0.1", "mir4nft", "mir4nft", "mir4nft");

// Fetch all transportID and class from the transports table
$result = $db->query("SELECT transportID, class FROM transports");
while ($row = $result->fetch_assoc()) {
    $transportID = $result['transportID'];
    $class = $result['class'];

    // Build the URL for fetching the skills info
    $url = "https://webapi.mir4global.com/nft/character/skills?" . http_build_query([
        'seq' => $transportID,
        'transportID' => $transportID,
        'class' => $class,
        'languageCode' => 'en',
    ]);
    echo ("Fetching skills for transportID $transportID\n");
    // Fetch the skills info
    $response = file_get_contents($url);
    $response_esc = mysqli_real_escape_string($db, $response);
    $query = "INSERT INTO `skills` (`transportID`, `json`) VALUES ('$transportID', '$response_esc') ON DUPLICATE KEY UPDATE `json` = '$response_esc'";
    $db->query($query);
}
