<?php

namespace DataShaman\Claude\AgentSdk\Exception;

class SessionNotFoundException extends ClaudeException
{
    public function __construct(string $sessionId)
    {
        parent::__construct("Session not found: {$sessionId}");
    }
}
