<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Tests;

use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;
use DataShaman\Claude\AgentSdk\Enum\PermissionMode;
use PHPUnit\Framework\TestCase;

final class ClaudeAgentOptionsTest extends TestCase
{
    public function testCreateReturnsDefaults(): void
    {
        $options = ClaudeAgentOptions::create();

        $this->assertNull($options->model);
        $this->assertNull($options->maxTurns);
        $this->assertNull($options->systemPrompt);
        $this->assertNull($options->appendSystemPrompt);
        $this->assertSame([], $options->tools);
        $this->assertSame([], $options->mcpServers);
        $this->assertNull($options->permissionMode);
        $this->assertSame([], $options->allowedTools);
        $this->assertNull($options->cwd);
        $this->assertSame([], $options->env);
        $this->assertNull($options->sessionId);
        $this->assertNull($options->extendedThinking);
        $this->assertNull($options->permissionPromptHandler);
    }

    public function testFluentBuilderReturnsNewInstance(): void
    {
        $original = ClaudeAgentOptions::create();
        $modified = $original->model('claude-sonnet-4-6');

        $this->assertNull($original->model);
        $this->assertSame('claude-sonnet-4-6', $modified->model);
        $this->assertNotSame($original, $modified);
    }

    public function testFluentChainingPreservesAllValues(): void
    {
        $options = ClaudeAgentOptions::create()
            ->model('claude-sonnet-4-6')
            ->maxTurns(5)
            ->systemPrompt('Be helpful')
            ->appendSystemPrompt('Extra')
            ->permissionMode(PermissionMode::AcceptEdits)
            ->allowedTools(['Read', 'Glob'])
            ->cwd('/tmp')
            ->env(['KEY' => 'value'])
            ->sessionId('sess-123');

        $this->assertSame('claude-sonnet-4-6', $options->model);
        $this->assertSame(5, $options->maxTurns);
        $this->assertSame('Be helpful', $options->systemPrompt);
        $this->assertSame('Extra', $options->appendSystemPrompt);
        $this->assertSame(PermissionMode::AcceptEdits, $options->permissionMode);
        $this->assertSame(['Read', 'Glob'], $options->allowedTools);
        $this->assertSame('/tmp', $options->cwd);
        $this->assertSame(['KEY' => 'value'], $options->env);
        $this->assertSame('sess-123', $options->sessionId);
    }

    public function testExtendedThinking(): void
    {
        $options = ClaudeAgentOptions::create()
            ->extendedThinking(['enabled' => true, 'budgetTokens' => 10000]);

        $this->assertSame(['enabled' => true, 'budgetTokens' => 10000], $options->extendedThinking);
    }

    public function testPermissionPromptHandler(): void
    {
        $handler = fn (array $request) => true;
        $options = ClaudeAgentOptions::create()
            ->permissionPromptHandler($handler);

        $this->assertSame($handler, $options->permissionPromptHandler);
    }
}
