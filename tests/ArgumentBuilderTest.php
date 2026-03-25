<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Tests;

use DataShaman\Claude\AgentSdk\ArgumentBuilder;
use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;
use DataShaman\Claude\AgentSdk\Enum\PermissionMode;
use PHPUnit\Framework\TestCase;

final class ArgumentBuilderTest extends TestCase
{
    public function testDefaultArgs(): void
    {
        $options = ClaudeAgentOptions::create();
        $args = ArgumentBuilder::build($options);

        $this->assertSame(['-p', '--output-format', 'stream-json', '--verbose'], $args);
    }

    public function testModelArg(): void
    {
        $options = ClaudeAgentOptions::create()->model('claude-sonnet-4-6');
        $args = ArgumentBuilder::build($options);

        $this->assertContains('--model', $args);
        $idx = array_search('--model', $args);
        $this->assertSame('claude-sonnet-4-6', $args[$idx + 1]);
    }

    public function testMaxTurnsArg(): void
    {
        $options = ClaudeAgentOptions::create()->maxTurns(5);
        $args = ArgumentBuilder::build($options);

        $this->assertContains('--max-turns', $args);
        $idx = array_search('--max-turns', $args);
        $this->assertSame('5', $args[$idx + 1]);
    }

    public function testSystemPromptArg(): void
    {
        $options = ClaudeAgentOptions::create()->systemPrompt('Be helpful');
        $args = ArgumentBuilder::build($options);

        $this->assertContains('--system-prompt', $args);
        $idx = array_search('--system-prompt', $args);
        $this->assertSame('Be helpful', $args[$idx + 1]);
    }

    public function testAppendSystemPromptArg(): void
    {
        $options = ClaudeAgentOptions::create()->appendSystemPrompt('Extra instructions');
        $args = ArgumentBuilder::build($options);

        $this->assertContains('--append-system-prompt', $args);
        $idx = array_search('--append-system-prompt', $args);
        $this->assertSame('Extra instructions', $args[$idx + 1]);
    }

    public function testPermissionModeArg(): void
    {
        $options = ClaudeAgentOptions::create()->permissionMode(PermissionMode::AcceptEdits);
        $args = ArgumentBuilder::build($options);

        $this->assertContains('--permission-mode', $args);
        $idx = array_search('--permission-mode', $args);
        $this->assertSame('acceptEdits', $args[$idx + 1]);
    }

    public function testAllowedToolsArg(): void
    {
        $options = ClaudeAgentOptions::create()->allowedTools(['Read', 'Glob']);
        $args = ArgumentBuilder::build($options);

        $this->assertContains('--allowedTools', $args);
        $idx = array_search('--allowedTools', $args);
        $this->assertSame('Read,Glob', $args[$idx + 1]);
    }

    public function testSessionIdArg(): void
    {
        $options = ClaudeAgentOptions::create()->sessionId('abc-123');
        $args = ArgumentBuilder::build($options);

        $this->assertContains('--resume', $args);
        $idx = array_search('--resume', $args);
        $this->assertSame('abc-123', $args[$idx + 1]);
    }

    public function testMcpServersArg(): void
    {
        $servers = ['myserver' => ['command' => 'node', 'args' => ['server.js']]];
        $options = ClaudeAgentOptions::create()->mcpServers($servers);
        $args = ArgumentBuilder::build($options);

        $this->assertContains('--mcp-config', $args);
        $idx = array_search('--mcp-config', $args);
        $decoded = json_decode($args[$idx + 1], true);
        $this->assertArrayHasKey('mcpServers', $decoded);
        $this->assertArrayHasKey('myserver', $decoded['mcpServers']);
    }

    public function testAllOptionsCombined(): void
    {
        $options = ClaudeAgentOptions::create()
            ->model('claude-sonnet-4-6')
            ->maxTurns(3)
            ->systemPrompt('Test')
            ->permissionMode(PermissionMode::BlockEdits);

        $args = ArgumentBuilder::build($options);

        $this->assertContains('--model', $args);
        $this->assertContains('--max-turns', $args);
        $this->assertContains('--system-prompt', $args);
        $this->assertContains('--permission-mode', $args);
    }
}
