<?php

namespace RPurinton\Mir4nft\OpenAI;

use OpenAI\Client as OpenAIClient;
use RPurinton\Mir4nft\{Config, Log};

class Client
{
    private ?string $api_key = null;
    private ?array $prompt = null;
    private ?OpenAIClient $ai = null;

    public function __construct(private Log $log)
    {
        $this->api_key = getenv('OPENAI_API_KEY') or throw new \Exception("OPENAI_API_KEY not found in environment");
        $this->prompt = Config::get('prompt') or throw new \Exception("prompt not found in config");
        $this->ai = \OpenAI::client($this->api_key) or throw new \Exception("failed to initialize OpenAI client");
        $this->log->debug("OpenAI client initialized");
    }

    public function complete(string $prompt): string
    {
        $this->log->debug("OpenAI client complete() called", ['prompt' => $prompt]);
        $this->prompt['prompt'] = $prompt;
        return $this->ai->completions()->create($this->prompt)->choices[0]->text;
    }
}
