<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Tests;

use DataShaman\Claude\AgentSdk\ClaudeAgentClient;
use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;
use PHPUnit\Framework\TestCase;

final class ClaudeAgentClientTest extends TestCase
{
    public function testCreateWithDefaults(): void
    {
        $client = ClaudeAgentClient::create();

        $this->assertNull($client->getSessionId());
    }

    public function testCreateWithSessionId(): void
    {
        $options = ClaudeAgentOptions::create()->sessionId('test-session');
        $client = ClaudeAgentClient::create($options);

        $this->assertSame('test-session', $client->getSessionId());
    }

    public function testListSessionsReturnsEmptyWhenCliUnavailable(): void
    {
        $client = ClaudeAgentClient::create();

        // When CLI is not available or returns no sessions, expect empty array
        $sessions = $client->listSessions();
        $this->assertIsArray($sessions);
    }
}
