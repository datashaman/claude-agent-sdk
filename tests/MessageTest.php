<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Tests;

use DataShaman\Claude\AgentSdk\Message\Message;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    public function testFromStreamEventWithNestedEvent(): void
    {
        $data = [
            'uuid' => 'test-uuid',
            'session_id' => 'session-123',
            'parent_tool_use_id' => null,
            'event' => [
                'type' => 'content_block_delta',
                'index' => 0,
                'delta' => ['type' => 'text_delta', 'text' => 'Hello'],
            ],
        ];

        $message = Message::fromStreamEvent($data);

        $this->assertSame('content_block_delta', $message->type);
        $this->assertSame(0, $message->index);
        $this->assertSame(['type' => 'text_delta', 'text' => 'Hello'], $message->delta);
        $this->assertSame('session-123', $message->sessionId);
        $this->assertSame('test-uuid', $message->uuid);
        $this->assertNull($message->parentToolUseId);
        $this->assertNull($message->message);
        $this->assertNull($message->contentBlock);
    }

    public function testFromStreamEventMessageStart(): void
    {
        $data = [
            'uuid' => 'uuid-1',
            'session_id' => 'sess-1',
            'event' => [
                'type' => 'message_start',
                'index' => 0,
                'message' => ['role' => 'assistant', 'content' => []],
            ],
        ];

        $message = Message::fromStreamEvent($data);

        $this->assertSame('message_start', $message->type);
        $this->assertSame(['role' => 'assistant', 'content' => []], $message->message);
    }

    public function testFromStreamEventContentBlockStart(): void
    {
        $data = [
            'uuid' => 'uuid-2',
            'session_id' => 'sess-2',
            'event' => [
                'type' => 'content_block_start',
                'index' => 0,
                'content_block' => ['type' => 'text'],
            ],
        ];

        $message = Message::fromStreamEvent($data);

        $this->assertSame('content_block_start', $message->type);
        $this->assertSame(['type' => 'text'], $message->contentBlock);
    }

    public function testFromStreamEventWithMissingFields(): void
    {
        $data = [
            'event' => ['type' => 'message_stop'],
        ];

        $message = Message::fromStreamEvent($data);

        $this->assertSame('message_stop', $message->type);
        $this->assertSame('', $message->sessionId);
        $this->assertSame('', $message->uuid);
        $this->assertNull($message->index);
    }
}
