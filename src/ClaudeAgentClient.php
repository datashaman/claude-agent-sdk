<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk;

use DataShaman\Claude\AgentSdk\Exception\SessionNotFoundException;
use DataShaman\Claude\AgentSdk\Message\Message;
use Generator;

final class ClaudeAgentClient
{
    private ?string $currentSessionId = null;

    public function __construct(
        private readonly ClaudeAgentOptions $options,
    ) {
        $this->currentSessionId = $options->sessionId;
    }

    public static function create(?ClaudeAgentOptions $options = null): self
    {
        return new self($options ?? ClaudeAgentOptions::create());
    }

    /** @return Generator<int, Message> */
    public function send(string $prompt): Generator
    {
        $options = $this->currentSessionId !== null
            ? $this->options->sessionId($this->currentSessionId)
            : $this->options;

        foreach (Claude::query($prompt, $options) as $message) {
            if ($this->currentSessionId === null && $message->sessionId !== '') {
                $this->currentSessionId = $message->sessionId;
            }
            yield $message;
        }
    }

    public function getSessionId(): ?string
    {
        return $this->currentSessionId;
    }

    /** @return list<array> */
    public function listSessions(): array
    {
        $output = $this->runCliCommand(['claude', 'sessions', 'list', '--json']);

        $sessions = json_decode($output, true);

        return is_array($sessions) ? $sessions : [];
    }

    /** @return list<Message> */
    public function getSessionMessages(string $sessionId): array
    {
        $output = $this->runCliCommand(['claude', 'sessions', 'get', $sessionId, '--json']);

        $data = json_decode($output, true);

        if ($data === null || (is_array($data) && isset($data['error']))) {
            throw new SessionNotFoundException($sessionId);
        }

        if (!is_array($data)) {
            throw new SessionNotFoundException($sessionId);
        }

        $messages = [];
        foreach ($data as $item) {
            if (is_array($item)) {
                $messages[] = Message::fromStreamEvent($item);
            }
        }

        return $messages;
    }

    private function runCliCommand(array $command): string
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            return '';
        }

        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        return $output;
    }
}
