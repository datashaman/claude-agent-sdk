<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk;

use DataShaman\Claude\AgentSdk\Enum\PermissionMode;

final class ClaudeAgentOptions
{
    /**
     * Environment variable keys that are excluded from passthrough by default.
     *
     * ANTHROPIC_API_KEY triggers direct API access (pay-per-use) instead of
     * subscription-based authentication via Claude Code. Since this SDK drives
     * the Claude CLI, which uses subscription auth, passing this key would
     * bypass the subscription and incur unexpected API charges.
     */
    public const DEFAULT_EXCLUDED_ENV_KEYS = [
        'ANTHROPIC_API_KEY',
    ];

    private function __construct(
        public readonly ?string $model = null,
        public readonly ?int $maxTurns = null,
        public readonly ?string $systemPrompt = null,
        public readonly ?string $appendSystemPrompt = null,
        public readonly array $tools = [],
        public readonly array $mcpServers = [],
        public readonly ?PermissionMode $permissionMode = null,
        public readonly array $allowedTools = [],
        public readonly ?string $cwd = null,
        public readonly array $env = [],
        public readonly ?string $sessionId = null,
        public readonly ?array $extendedThinking = null,
        /** @var callable|null */
        public readonly mixed $permissionPromptHandler = null,
        /** @var list<string> Environment variable keys to exclude from passthrough */
        public readonly array $excludeEnvKeys = self::DEFAULT_EXCLUDED_ENV_KEYS,
    ) {}

    public static function create(): self
    {
        return new self();
    }

    private function with(string $property, mixed $value): self
    {
        $args = [];
        $ref = new \ReflectionClass(self::class);
        foreach ($ref->getConstructor()->getParameters() as $param) {
            $name = $param->getName();
            $args[$name] = $name === $property ? $value : $this->{$name};
        }

        return new self(...$args);
    }

    public function model(string $model): self
    {
        return $this->with('model', $model);
    }

    public function maxTurns(int $maxTurns): self
    {
        return $this->with('maxTurns', $maxTurns);
    }

    public function systemPrompt(string $systemPrompt): self
    {
        return $this->with('systemPrompt', $systemPrompt);
    }

    public function appendSystemPrompt(string $appendSystemPrompt): self
    {
        return $this->with('appendSystemPrompt', $appendSystemPrompt);
    }

    public function tools(array $tools): self
    {
        return $this->with('tools', $tools);
    }

    public function mcpServers(array $mcpServers): self
    {
        return $this->with('mcpServers', $mcpServers);
    }

    public function permissionMode(PermissionMode $permissionMode): self
    {
        return $this->with('permissionMode', $permissionMode);
    }

    public function allowedTools(array $allowedTools): self
    {
        return $this->with('allowedTools', $allowedTools);
    }

    public function cwd(string $cwd): self
    {
        return $this->with('cwd', $cwd);
    }

    public function env(array $env): self
    {
        return $this->with('env', $env);
    }

    public function sessionId(string $sessionId): self
    {
        return $this->with('sessionId', $sessionId);
    }

    public function extendedThinking(array $extendedThinking): self
    {
        return $this->with('extendedThinking', $extendedThinking);
    }

    public function permissionPromptHandler(callable $permissionPromptHandler): self
    {
        return $this->with('permissionPromptHandler', $permissionPromptHandler);
    }

    /**
     * Set environment variable keys to exclude from passthrough to the CLI.
     *
     * @param list<string> $excludeEnvKeys
     */
    public function excludeEnvKeys(array $excludeEnvKeys): self
    {
        return $this->with('excludeEnvKeys', $excludeEnvKeys);
    }
}
