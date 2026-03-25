<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Tests;

use DataShaman\Claude\AgentSdk\Attribute\Parameter;
use DataShaman\Claude\AgentSdk\Attribute\Tool;
use DataShaman\Claude\AgentSdk\Mcp\McpServer;
use PHPUnit\Framework\TestCase;

use function DataShaman\Claude\AgentSdk\Mcp\createSdkMcpServer;

#[Tool(name: 'echo_tool', description: 'Echo back the input')]
function echoTool(
    #[Parameter(description: 'Text to echo')]
    string $text,
): string {
    return $text;
}

final class McpServerTest extends TestCase
{
    public function testRegisterToolAndGetSchemas(): void
    {
        $server = new McpServer();
        $server->registerTool('DataShaman\Claude\AgentSdk\Tests\echoTool');

        $schemas = $server->getToolSchemas();

        $this->assertArrayHasKey('echo_tool', $schemas);
        $this->assertSame('Echo back the input', $schemas['echo_tool']['description']);
    }

    public function testCreateSdkMcpServerFunction(): void
    {
        $server = createSdkMcpServer([
            'DataShaman\Claude\AgentSdk\Tests\echoTool',
        ]);

        $schemas = $server->getToolSchemas();
        $this->assertArrayHasKey('echo_tool', $schemas);
    }

    public function testMcpServersInOptions(): void
    {
        $options = \DataShaman\Claude\AgentSdk\ClaudeAgentOptions::create()
            ->mcpServers([
                'myserver' => ['command' => 'php', 'args' => ['server.php']],
            ]);

        $this->assertSame(
            ['myserver' => ['command' => 'php', 'args' => ['server.php']]],
            $options->mcpServers,
        );
    }
}
