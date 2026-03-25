<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Tests;

use DataShaman\Claude\AgentSdk\ArgumentBuilder;
use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;
use DataShaman\Claude\AgentSdk\Enum\PermissionMode;
use PHPUnit\Framework\TestCase;

final class PermissionTest extends TestCase
{
    public function testDefaultPermissionModeOmitsFlag(): void
    {
        $options = ClaudeAgentOptions::create();
        $args = ArgumentBuilder::build($options);

        $this->assertNotContains('--permission-mode', $args);
    }

    public function testAcceptEditsPermissionMode(): void
    {
        $options = ClaudeAgentOptions::create()->permissionMode(PermissionMode::AcceptEdits);
        $args = ArgumentBuilder::build($options);

        $idx = array_search('--permission-mode', $args);
        $this->assertNotFalse($idx);
        $this->assertSame('acceptEdits', $args[$idx + 1]);
    }

    public function testBlockEditsPermissionMode(): void
    {
        $options = ClaudeAgentOptions::create()->permissionMode(PermissionMode::BlockEdits);
        $args = ArgumentBuilder::build($options);

        $idx = array_search('--permission-mode', $args);
        $this->assertSame('blockEdits', $args[$idx + 1]);
    }

    public function testBypassPermissionsMode(): void
    {
        $options = ClaudeAgentOptions::create()->permissionMode(PermissionMode::BypassPermissions);
        $args = ArgumentBuilder::build($options);

        $idx = array_search('--permission-mode', $args);
        $this->assertSame('bypassPermissions', $args[$idx + 1]);
    }

    public function testAllowedToolsPassthrough(): void
    {
        $options = ClaudeAgentOptions::create()->allowedTools(['Read', 'Write', 'Bash']);
        $args = ArgumentBuilder::build($options);

        $idx = array_search('--allowedTools', $args);
        $this->assertNotFalse($idx);
        $this->assertSame('Read,Write,Bash', $args[$idx + 1]);
    }

    public function testEmptyAllowedToolsOmitsFlag(): void
    {
        $options = ClaudeAgentOptions::create()->allowedTools([]);
        $args = ArgumentBuilder::build($options);

        $this->assertNotContains('--allowedTools', $args);
    }

    public function testPermissionPromptHandlerStoredInOptions(): void
    {
        $called = false;
        $handler = function (array $request) use (&$called) {
            $called = true;
            return true;
        };

        $options = ClaudeAgentOptions::create()->permissionPromptHandler($handler);

        $this->assertNotNull($options->permissionPromptHandler);
        ($options->permissionPromptHandler)(['tool' => 'Bash', 'command' => 'ls']);
        $this->assertTrue($called);
    }

    public function testAllPermissionModeEnumValues(): void
    {
        $cases = PermissionMode::cases();

        $this->assertCount(4, $cases);
        $this->assertSame('default', PermissionMode::Default->value);
        $this->assertSame('acceptEdits', PermissionMode::AcceptEdits->value);
        $this->assertSame('blockEdits', PermissionMode::BlockEdits->value);
        $this->assertSame('bypassPermissions', PermissionMode::BypassPermissions->value);
    }
}
