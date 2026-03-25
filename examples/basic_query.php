<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DataShaman\Claude\AgentSdk\Claude;
use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;

$options = ClaudeAgentOptions::create()
    ->model('claude-sonnet-4-6')
    ->maxTurns(3);

echo "Querying Claude...\n\n";

foreach (Claude::query('Explain PHP generators in 3 sentences.', $options) as $message) {
    if ($message->type === 'content_block_delta' && isset($message->delta['text'])) {
        echo $message->delta['text'];
    }
}

echo "\n";
