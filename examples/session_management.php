<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DataShaman\Claude\AgentSdk\ClaudeAgentClient;
use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;

$client = ClaudeAgentClient::create(
    ClaudeAgentOptions::create()->model('claude-sonnet-4-6')
);

// First turn
echo "Turn 1:\n";
foreach ($client->send('My name is Alice. Remember it.') as $message) {
    if ($message->type === 'content_block_delta' && isset($message->delta['text'])) {
        echo $message->delta['text'];
    }
}

echo "\n\nTurn 2:\n";
// Second turn — same session, Claude remembers the name
foreach ($client->send('What is my name?') as $message) {
    if ($message->type === 'content_block_delta' && isset($message->delta['text'])) {
        echo $message->delta['text'];
    }
}

echo "\n\nSession ID: " . $client->getSessionId() . "\n";
