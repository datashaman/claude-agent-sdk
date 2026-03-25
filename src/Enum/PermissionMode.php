<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Enum;

enum PermissionMode: string
{
    case Default = 'default';
    case AcceptEdits = 'acceptEdits';
    case BlockEdits = 'blockEdits';
    case BypassPermissions = 'bypassPermissions';
}
