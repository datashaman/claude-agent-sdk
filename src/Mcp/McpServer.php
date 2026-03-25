<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Mcp;

use DataShaman\Claude\AgentSdk\ToolRegistry;

final class McpServer
{
    private ToolRegistry $registry;
    private bool $running = false;

    public function __construct()
    {
        $this->registry = new ToolRegistry();
    }

    public function registerTool(callable $tool): void
    {
        $this->registry->register($tool);
    }

    public function run(): void
    {
        $this->running = true;
        $stdin = fopen('php://stdin', 'r');
        $stdout = fopen('php://stdout', 'w');

        if ($stdin === false || $stdout === false) {
            return;
        }

        while ($this->running) {
            $line = fgets($stdin);

            if ($line === false) {
                break;
            }

            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $request = json_decode($line, true);
            if ($request === null) {
                continue;
            }

            $response = $this->handleRequest($request);
            fwrite($stdout, json_encode($response) . "\n");
            fflush($stdout);
        }

        fclose($stdin);
        fclose($stdout);
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function getToolSchemas(): array
    {
        return $this->registry->getAllSchemas();
    }

    private function handleRequest(array $request): array
    {
        $method = $request['method'] ?? '';
        $id = $request['id'] ?? null;

        return match ($method) {
            'initialize' => $this->handleInitialize($id),
            'tools/list' => $this->handleToolsList($id),
            'tools/call' => $this->handleToolsCall($id, $request['params'] ?? []),
            default => [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => ['code' => -32601, 'message' => "Method not found: {$method}"],
            ],
        };
    }

    private function handleInitialize(mixed $id): array
    {
        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => [
                'protocolVersion' => '2024-11-05',
                'capabilities' => ['tools' => []],
                'serverInfo' => [
                    'name' => 'claude-agent-sdk-php',
                    'version' => '1.0.0',
                ],
            ],
        ];
    }

    private function handleToolsList(mixed $id): array
    {
        $tools = [];
        foreach ($this->registry->getAllSchemas() as $name => $schema) {
            $tools[] = [
                'name' => $name,
                'description' => $schema['description'],
                'inputSchema' => $schema['input_schema'],
            ];
        }

        return [
            'jsonrpc' => '2.0',
            'id' => $id,
            'result' => ['tools' => $tools],
        ];
    }

    private function handleToolsCall(mixed $id, array $params): array
    {
        $name = $params['name'] ?? '';
        $arguments = $params['arguments'] ?? [];

        if (!$this->registry->has($name)) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => ['code' => -32602, 'message' => "Tool not found: {$name}"],
            ];
        }

        try {
            $result = $this->registry->execute($name, $arguments);
            $content = is_string($result) ? $result : json_encode($result);

            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [
                    'content' => [['type' => 'text', 'text' => $content]],
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => [
                    'isError' => true,
                    'content' => [['type' => 'text', 'text' => $e->getMessage()]],
                ],
            ];
        }
    }
}
