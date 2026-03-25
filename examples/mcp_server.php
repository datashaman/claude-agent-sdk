<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DataShaman\Claude\AgentSdk\Attribute\Parameter;
use DataShaman\Claude\AgentSdk\Attribute\Tool;

use function DataShaman\Claude\AgentSdk\Mcp\createSdkMcpServer;

#[Tool(name: 'get_time', description: 'Get the current time')]
function getTime(
    #[Parameter(description: 'Timezone', enum: ['UTC', 'US/Eastern', 'US/Pacific', 'Europe/London'])]
    string $timezone = 'UTC',
): string {
    $dt = new DateTimeImmutable('now', new DateTimeZone($timezone));
    return $dt->format('Y-m-d H:i:s T');
}

#[Tool(name: 'reverse_string', description: 'Reverse a string')]
function reverseString(
    #[Parameter(description: 'The string to reverse')]
    string $input,
): string {
    return strrev($input);
}

// Create and run the MCP server
$server = createSdkMcpServer([
    'getTime',
    'reverseString',
]);

// This blocks and listens on stdio
$server->run();
