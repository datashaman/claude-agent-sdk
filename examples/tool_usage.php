<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DataShaman\Claude\AgentSdk\Attribute\Parameter;
use DataShaman\Claude\AgentSdk\Attribute\Tool;
use DataShaman\Claude\AgentSdk\Claude;
use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;

#[Tool(name: 'calculate', description: 'Perform a math calculation')]
function calculate(
    #[Parameter(description: 'Mathematical expression to evaluate')]
    string $expression,
): string {
    // Simple eval for demonstration — use a proper math parser in production
    $result = eval("return {$expression};");
    return "Result: {$result}";
}

$options = ClaudeAgentOptions::create()
    ->tools(['calculate']);

foreach (Claude::query('What is 42 * 17 + 3?', $options) as $message) {
    if ($message->type === 'content_block_delta' && isset($message->delta['text'])) {
        echo $message->delta['text'];
    }
}

echo "\n";
