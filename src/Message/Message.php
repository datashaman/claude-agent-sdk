<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Message;

final readonly class Message
{
    public function __construct(
        public string $type,
        public ?int $index,
        public ?array $message,
        public ?array $contentBlock,
        public ?array $delta,
        public string $sessionId,
        public string $uuid,
        public ?string $parentToolUseId,
    ) {}

    public static function fromStreamEvent(array $data): self
    {
        $event = $data['event'] ?? $data;

        return new self(
            type: $event['type'] ?? '',
            index: $event['index'] ?? null,
            message: $event['message'] ?? null,
            contentBlock: $event['content_block'] ?? null,
            delta: $event['delta'] ?? null,
            sessionId: $data['session_id'] ?? '',
            uuid: $data['uuid'] ?? '',
            parentToolUseId: $data['parent_tool_use_id'] ?? null,
        );
    }
}
