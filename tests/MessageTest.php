<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Tests;

use DataShaman\Claude\AgentSdk\Message\Message;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    public function testFromStreamEventAssistantMessage(): void
    {
        $data = [
            'type' => 'assistant',
            'message' => [
                'role' => 'assistant',
                'content' => [['type' => 'text', 'text' => 'Hello!']],
            ],
            'session_id' => 'session-123',
            'uuid' => 'test-uuid',
            'parent_tool_use_id' => null,
        ];

        $message = Message::fromStreamEvent($data);

        $this->assertSame('assistant', $message->type);
        $this->assertNull($message->subtype);
        $this->assertSame('session-123', $message->sessionId);
        $this->assertSame('test-uuid', $message->uuid);
        $this->assertNull($message->parentToolUseId);
        $this->assertSame('Hello!', $message->getTextContent());
    }

    public function testFromStreamEventSystemMessage(): void
    {
        $data = [
            'type' => 'system',
            'subtype' => 'init',
            'session_id' => 'sess-1',
            'uuid' => 'uuid-1',
        ];

        $message = Message::fromStreamEvent($data);

        $this->assertSame('system', $message->type);
        $this->assertSame('init', $message->subtype);
    }

    public function testFromStreamEventResultMessage(): void
    {
        $data = [
            'type' => 'result',
            'subtype' => 'success',
            'result' => 'Hello world!',
            'total_cost_usd' => 0.05,
            'duration_ms' => 1234,
            'session_id' => 'sess-2',
            'uuid' => 'uuid-2',
        ];

        $message = Message::fromStreamEvent($data);

        $this->assertSame('result', $message->type);
        $this->assertSame('success', $message->subtype);
        $this->assertSame('Hello world!', $message->result);
        $this->assertSame(0.05, $message->costUsd);
        $this->assertSame(1234, $message->durationMs);
        $this->assertSame('Hello world!', $message->getTextContent());
    }

    public function testGetTextContentReturnsNullForSystemMessages(): void
    {
        $data = [
            'type' => 'system',
            'subtype' => 'init',
            'uuid' => 'uuid-3',
            'session_id' => 'sess-3',
        ];

        $message = Message::fromStreamEvent($data);
        $this->assertNull($message->getTextContent());
    }

    public function testFromStreamEventWithMissingFields(): void
    {
        $data = ['type' => 'system'];

        $message = Message::fromStreamEvent($data);

        $this->assertSame('system', $message->type);
        $this->assertSame('', $message->sessionId);
        $this->assertSame('', $message->uuid);
        $this->assertNull($message->result);
        $this->assertNull($message->costUsd);
    }
}
