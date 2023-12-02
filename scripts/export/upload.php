<?php
$file_ids = [];
$cli = "curl https://api.openai.com/v1/files   -H \"Authorization: Bearer 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\"   -F \"purpose=fine-tune\"   -F \"file=@data.jsonl\"";
$json_string = shell_exec($cli);
$json = json_decode($json_string, true);
$file_ids[] = $json["id"];
file_put_contents("file_ids.json", json_encode($file_ids, JSON_PRETTY_PRINT));
