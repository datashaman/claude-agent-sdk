<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk;

use DataShaman\Claude\AgentSdk\Exception\ClaudeNotFoundException;
use DataShaman\Claude\AgentSdk\Exception\ClaudeProcessException;
use DataShaman\Claude\AgentSdk\Message\Message;
use Generator;

final class ClaudeProcess
{
    /** @var resource|null */
    private $process = null;

    /** @var array<int, resource> */
    private array $pipes = [];

    private string $stderr = '';

    public function __construct(
        private readonly ClaudeAgentOptions $options,
        private readonly string $prompt,
        private readonly ?ToolRegistry $toolRegistry = null,
    ) {}

    public function __destruct()
    {
        $this->close();
    }

    /** @return Generator<int, Message> */
    public function stream(): Generator
    {
        $command = $this->buildCommand();
        $this->spawn($command);

        $stdout = $this->pipes[1];
        stream_set_blocking($stdout, false);

        while (true) {
            $line = fgets($stdout);

            if ($line === false) {
                if (feof($stdout)) {
                    break;
                }
                usleep(1000);
                continue;
            }

            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $data = json_decode($line, true);
            if ($data === null) {
                continue;
            }

            $message = Message::fromStreamEvent($data);

            // Handle tool execution if registry is available
            if ($this->toolRegistry !== null && $this->isToolUseComplete($message, $data)) {
                $this->handleToolExecution($data);
            }

            yield $message;
        }

        $this->collectStderr();
        $exitCode = $this->close();

        if ($exitCode !== 0 && $exitCode !== null) {
            throw new ClaudeProcessException($exitCode, $this->stderr);
        }
    }

    public function write(string $data): void
    {
        if (isset($this->pipes[0]) && is_resource($this->pipes[0])) {
            fwrite($this->pipes[0], $data . "\n");
            fflush($this->pipes[0]);
        }
    }

    public function close(): ?int
    {
        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
        $this->pipes = [];

        if ($this->process !== null && is_resource($this->process)) {
            $status = proc_close($this->process);
            $this->process = null;
            return $status;
        }

        return null;
    }

    private function spawn(array $command): void
    {
        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $this->process = proc_open(
            $command,
            $descriptors,
            $this->pipes,
            $this->options->cwd,
            $this->options->env ?: null,
        );

        if (!is_resource($this->process)) {
            throw new ClaudeProcessException(-1, 'Failed to start Claude CLI process');
        }
    }

    /** @return list<string> */
    private function buildCommand(): array
    {
        $binary = $this->findBinary();
        $args = ArgumentBuilder::build($this->options);

        return [$binary, ...$args, '--', $this->prompt];
    }

    private function findBinary(): string
    {
        $paths = [
            getenv('CLAUDE_CLI_PATH') ?: null,
            'claude',
        ];

        foreach ($paths as $path) {
            if ($path === null) {
                continue;
            }

            $result = shell_exec("which {$path} 2>/dev/null");
            if ($result !== null && trim($result) !== '') {
                return trim($result);
            }
        }

        throw new ClaudeNotFoundException();
    }

    private function isToolUseComplete(Message $message, array $rawData): bool
    {
        if ($message->type !== 'content_block_stop') {
            return false;
        }

        // Check if the stopped content block was a tool_use type
        return isset($rawData['event']['content_block']['type'])
            && $rawData['event']['content_block']['type'] === 'tool_use';
    }

    private function handleToolExecution(array $rawData): void
    {
        $contentBlock = $rawData['event']['content_block'] ?? null;
        if ($contentBlock === null || !isset($contentBlock['name'], $contentBlock['input'])) {
            return;
        }

        $toolName = $contentBlock['name'];
        $arguments = $contentBlock['input'];
        $toolUseId = $contentBlock['id'] ?? '';

        if (!$this->toolRegistry->has($toolName)) {
            $this->sendToolResult($toolUseId, null, "Tool not found: {$toolName}");
            return;
        }

        try {
            $result = $this->toolRegistry->execute($toolName, $arguments);
            $this->sendToolResult($toolUseId, $result);
        } catch (\Throwable $e) {
            $this->sendToolResult($toolUseId, null, $e->getMessage());
        }
    }

    private function sendToolResult(string $toolUseId, mixed $result, ?string $error = null): void
    {
        $response = [
            'type' => 'tool_result',
            'tool_use_id' => $toolUseId,
        ];

        if ($error !== null) {
            $response['is_error'] = true;
            $response['content'] = $error;
        } else {
            $response['content'] = is_string($result) ? $result : json_encode($result);
        }

        $this->write(json_encode($response));
    }

    private function collectStderr(): void
    {
        if (isset($this->pipes[2]) && is_resource($this->pipes[2])) {
            $this->stderr = stream_get_contents($this->pipes[2]) ?: '';
        }
    }
}
