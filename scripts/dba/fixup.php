<?php

// Example when we needed to fix the skills table.
// Can be used as a template for future DB fixes.

// Database connection
$db = mysqli_connect("127.0.0.1", "mir4nft", "mir4nft", "mir4nft");

// Fetch all transportID and class from the transports table
$result = $db->query("SELECT transportID, class FROM transports");
while ($row = $result->fetch_assoc()) {
    $transportID = $row['transportID'];
    $class = $row['class'];

    // Build the URL for fetching the skills info
    $url = "https://webapi.mir4global.com/nft/character/skills?" . http_build_query([
        'transportID' => $transportID,
        'class' => $class,
        'languageCode' => 'en',
    ]);
    echo ("Fetching skills for transportID $transportID\n");
    // Fetch the skills info
    $response = geturl($url);
    $response_esc = mysqli_real_escape_string($db, $response);
    $query = "INSERT INTO `skills` (`transportID`, `json`) VALUES ('$transportID', '$response_esc') ON DUPLICATE KEY UPDATE `json` = '$response_esc'";
    $db->query($query);
}

function geturl(string $url, array $headers = []): string
{
    $headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.82 Safari/537.36";
    $response = file_get_contents($url, false, stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers)
        ]
    ])) or throw new Error("failed to get contents");
    return $response;
}
