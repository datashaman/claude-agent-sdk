<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Mcp;

function createSdkMcpServer(array $tools = []): McpServer
{
    $server = new McpServer();

    foreach ($tools as $tool) {
        $server->registerTool($tool);
    }

    return $server;
}
