<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk;

use DataShaman\Claude\AgentSdk\Message\Message;
use Generator;

final class Claude
{
    /** @return Generator<int, Message> */
    public static function query(string $prompt, ?ClaudeAgentOptions $options = null): Generator
    {
        $options ??= ClaudeAgentOptions::create();
        $toolRegistry = self::buildToolRegistry($options);

        $process = new ClaudeProcess($options, $prompt, $toolRegistry);

        yield from $process->stream();
    }

    private static function buildToolRegistry(ClaudeAgentOptions $options): ?ToolRegistry
    {
        if (empty($options->tools)) {
            return null;
        }

        $registry = new ToolRegistry();
        foreach ($options->tools as $tool) {
            $registry->register($tool);
        }

        return $registry;
    }
}
