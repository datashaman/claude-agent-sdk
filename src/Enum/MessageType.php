<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Enum;

enum MessageType: string
{
    case MessageStart = 'message_start';
    case ContentBlockStart = 'content_block_start';
    case ContentBlockDelta = 'content_block_delta';
    case ContentBlockStop = 'content_block_stop';
    case MessageDelta = 'message_delta';
    case MessageStop = 'message_stop';
    case System = 'system';
}
