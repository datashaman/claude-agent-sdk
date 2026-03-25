<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DataShaman\Claude\AgentSdk\Claude;
use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;
use DataShaman\Claude\AgentSdk\Enum\PermissionMode;

$options = ClaudeAgentOptions::create()
    ->model('claude-sonnet-4-6')
    ->maxTurns(1)
    ->permissionMode(PermissionMode::Default)
    ->systemPrompt('You are a helpful assistant. Keep responses brief.');

$prompt = $argv[1] ?? 'Say hello in 10 words or less.';

echo "Prompt: {$prompt}\n";
echo "Model: {$options->model}\n";
echo str_repeat('-', 40) . "\n";

foreach (Claude::query($prompt, $options) as $message) {
    $text = $message->getTextContent();
    if ($text !== null) {
        echo $text;
    }
}

echo "\n";
