<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk;

final class ArgumentBuilder
{
    /** @return list<string> */
    public static function build(ClaudeAgentOptions $options): array
    {
        $args = [
            '-p',
            '--output-format', 'stream-json',
            '--verbose',
        ];

        if ($options->bare) {
            $args[] = '--bare';
        }

        if ($options->model !== null) {
            $args[] = '--model';
            $args[] = $options->model;
        }

        if ($options->maxTurns !== null) {
            $args[] = '--max-turns';
            $args[] = (string) $options->maxTurns;
        }

        if ($options->systemPrompt !== null) {
            $args[] = '--system-prompt';
            $args[] = $options->systemPrompt;
        }

        if ($options->appendSystemPrompt !== null) {
            $args[] = '--append-system-prompt';
            $args[] = $options->appendSystemPrompt;
        }

        if ($options->permissionMode !== null) {
            $args[] = '--permission-mode';
            $args[] = $options->permissionMode->value;
        }

        if (!empty($options->allowedTools)) {
            $args[] = '--allowedTools';
            $args[] = implode(',', $options->allowedTools);
        }

        if ($options->sessionId !== null) {
            $args[] = '--resume';
            $args[] = $options->sessionId;
        }

        if (!empty($options->mcpServers)) {
            $args[] = '--mcp-config';
            $args[] = json_encode(['mcpServers' => $options->mcpServers]);
        }

        return $args;
    }
}
