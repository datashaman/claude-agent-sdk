## ADDED Requirements

### Requirement: ClaudeAgentOptions value object
The SDK SHALL provide a `ClaudeAgentOptions` class with readonly properties for all configuration: model, maxTurns, systemPrompt, appendSystemPrompt, tools, mcpServers, permissionMode, allowedTools, cwd, and env.

#### Scenario: Create options with fluent builder
- **WHEN** the developer chains builder methods like `ClaudeAgentOptions::create()->model('claude-sonnet-4-6')->maxTurns(5)`
- **THEN** a new options instance is returned with the specified values and defaults for unset properties

#### Scenario: Immutable options
- **WHEN** the developer calls a builder method on an existing options instance
- **THEN** a new instance is returned; the original instance is unchanged

### Requirement: Model selection
The SDK SHALL support specifying the Claude model via the `model` option, defaulting to the CLI's default model.

#### Scenario: Specify model
- **WHEN** the developer sets `model` to `'claude-sonnet-4-6'`
- **THEN** the SDK passes `--model claude-sonnet-4-6` to the CLI

#### Scenario: Default model
- **WHEN** no model is specified
- **THEN** the SDK does not pass a `--model` flag, letting the CLI use its default

### Requirement: Extended thinking configuration
The SDK SHALL support enabling extended thinking via the `extendedThinking` option with configurable budget tokens.

#### Scenario: Enable extended thinking
- **WHEN** the developer sets `extendedThinking` to `['enabled' => true, 'budgetTokens' => 10000]`
- **THEN** the SDK passes the extended thinking configuration to the CLI

### Requirement: Working directory and environment
The SDK SHALL support setting the working directory (`cwd`) and environment variables (`env`) for the Claude CLI subprocess.

#### Scenario: Custom working directory
- **WHEN** the developer sets `cwd` to `/path/to/project`
- **THEN** the CLI process is spawned with that working directory

#### Scenario: Custom environment variables
- **WHEN** the developer sets `env` to `['ANTHROPIC_API_KEY' => 'sk-...']`
- **THEN** those environment variables are available to the CLI process

### Requirement: Max turns limit
The SDK SHALL support limiting the number of agent turns via the `maxTurns` option.

#### Scenario: Max turns reached
- **WHEN** the agent reaches the configured `maxTurns` limit
- **THEN** the session ends and the generator completes
