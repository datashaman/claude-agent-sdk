<?php

namespace DataShaman\Claude\AgentSdk\Exception;

class ClaudeNotFoundException extends ClaudeException
{
    public function __construct()
    {
        parent::__construct('Claude CLI binary not found. Please install Claude Code: https://docs.anthropic.com/en/docs/claude-code/overview');
    }
}
