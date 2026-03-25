<?php

namespace DataShaman\Claude\AgentSdk\Exception;

class ClaudeProcessException extends ClaudeException
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $stderr,
    ) {
        parent::__construct("Claude CLI process exited with code {$exitCode}: {$stderr}");
    }
}
