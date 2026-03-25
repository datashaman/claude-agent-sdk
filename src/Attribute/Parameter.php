<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Parameter
{
    public function __construct(
        public readonly ?string $description = null,
        public readonly ?string $type = null,
        public readonly ?array $enum = null,
    ) {
    }
}
