<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Message;

final readonly class Message
{
    public function __construct(
        public string $type,
        public ?string $subtype,
        public ?array $message,
        public ?array $delta,
        public string $sessionId,
        public string $uuid,
        public ?string $parentToolUseId,
        public ?string $result,
        public ?float $costUsd,
        public ?int $durationMs,
    ) {}

    public static function fromStreamEvent(array $data): self
    {
        return new self(
            type: $data['type'] ?? '',
            subtype: $data['subtype'] ?? null,
            message: $data['message'] ?? null,
            delta: $data['delta'] ?? null,
            sessionId: $data['session_id'] ?? '',
            uuid: $data['uuid'] ?? '',
            parentToolUseId: $data['parent_tool_use_id'] ?? null,
            result: $data['result'] ?? null,
            costUsd: $data['total_cost_usd'] ?? null,
            durationMs: $data['duration_ms'] ?? null,
        );
    }

    public function getTextContent(): ?string
    {
        if ($this->type === 'assistant' && isset($this->message['content'])) {
            $parts = [];
            foreach ($this->message['content'] as $block) {
                if (($block['type'] ?? '') === 'text') {
                    $parts[] = $block['text'];
                }
            }
            return $parts ? implode('', $parts) : null;
        }

        if ($this->type === 'result') {
            return $this->result;
        }

        return null;
    }
}
