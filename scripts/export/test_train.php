<?php
$data["training_file"] = "file-DTHcNkPEnFUiZZQsg4Vxy3mC";
$data["model"] = "gpt-3.5-turbo-0613";
$json_string = json_encode($data);
$cli = "curl https://api.openai.com/v1/fine_tuning/jobs/list -H 'Content-Type: application/json' -H 'Authorization: Bearer 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'";
passthru($cli);
