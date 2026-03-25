## 1. Project Scaffolding

- [x] 1.1 Create `composer.json` with package name `datashaman/claude-agent-sdk`, PSR-4 autoload for `DataShaman\Claude\AgentSdk` namespace, PHP 8.2+ requirement, and PHPUnit dev dependency
- [x] 1.2 Create directory structure: `src/`, `tests/`, `src/Attribute/`, `src/Exception/`, `src/Enum/`, `src/Message/`
- [x] 1.3 Create `phpunit.xml.dist` configuration
- [x] 1.4 Create `.gitignore` for vendor/, composer.lock, .phpunit.cache/

## 2. Enums and Value Objects

- [x] 2.1 Create `PermissionMode` enum with cases: Default, AcceptEdits, BlockEdits, BypassPermissions
- [x] 2.2 Create `MessageType` enum for message type discrimination (assistant, user, tool_use, tool_result, system, etc.)
- [x] 2.3 Create `Message` readonly class with type, content, and metadata properties
- [x] 2.4 Create `ClaudeAgentOptions` readonly class with fluent builder methods for all config (model, maxTurns, systemPrompt, appendSystemPrompt, tools, mcpServers, permissionMode, allowedTools, cwd, env, sessionId, extendedThinking)

## 3. Exceptions

- [x] 3.1 Create `ClaudeException` base exception class
- [x] 3.2 Create `ClaudeNotFoundException` for missing CLI binary
- [x] 3.3 Create `ClaudeProcessException` for non-zero exit codes (includes exit code and stderr)
- [x] 3.4 Create `SessionNotFoundException` for invalid session IDs

## 4. Tool System

- [x] 4.1 Create `#[Tool]` attribute class with name and description properties
- [x] 4.2 Create `#[Parameter]` attribute class with description, type override, and enum properties
- [x] 4.3 Create `ToolRegistry` class that collects `#[Tool]`-annotated callables
- [x] 4.4 Implement JSON Schema generation from tool reflection (parameter types, defaults, required fields)
- [x] 4.5 Implement tool execution: deserialize arguments, call function, serialize result
- [x] 4.6 Write tests for tool registration, schema generation, and execution

## 5. Process Management

- [x] 5.1 Create `ClaudeProcess` class wrapping `proc_open()` for spawning the CLI with pipes
- [x] 5.2 Implement CLI argument builder that translates `ClaudeAgentOptions` to command-line flags
- [x] 5.3 Implement JSON-line reader that parses stdout stream into message objects
- [x] 5.4 Implement stdin writer for sending tool results and conversation input back to the process
- [x] 5.5 Implement process cleanup (close pipes, wait for exit, handle signals)
- [x] 5.6 Implement CLI binary detection with helpful error message when not found
- [x] 5.7 Write tests for process lifecycle (mock process for unit tests)

## 6. Core Query Function

- [x] 6.1 Implement `Claude::query()` static method that creates a process, streams messages via Generator, and handles tool callbacks
- [x] 6.2 Implement conversation input support (passing message history)
- [x] 6.3 Implement max turns enforcement
- [x] 6.4 Write integration tests for basic query flow

## 7. Session Management

- [x] 7.1 Create `ClaudeAgentClient` class with constructor accepting `ClaudeAgentOptions`
- [x] 7.2 Implement `send()` method for multi-turn conversations within a session
- [x] 7.3 Implement `listSessions()` method calling `claude sessions list --json`
- [x] 7.4 Implement `getSessionMessages()` method for retrieving session history
- [x] 7.5 Implement session resume via `--resume` flag
- [x] 7.6 Write tests for session management

## 8. MCP Integration

- [x] 8.1 Implement `createSdkMcpServer()` function that creates an MCP server over stdio transport
- [x] 8.2 Implement MCP tool registration from `#[Tool]`-annotated callables
- [x] 8.3 Implement MCP server configuration in `ClaudeAgentOptions` (mcpServers option)
- [x] 8.4 Write tests for MCP server creation and tool exposure

## 9. Permission System

- [x] 9.1 Implement permission mode CLI flag mapping in argument builder
- [x] 9.2 Implement allowed tools configuration passthrough
- [x] 9.3 Implement `permissionPromptHandler` callback support for interactive permission decisions
- [x] 9.4 Write tests for permission configuration

## 10. Documentation and Distribution

- [x] 10.1 Write README.md with installation, quick start, and API reference
- [x] 10.2 Add usage examples: basic query, tool usage, session management, MCP server
- [x] 10.3 Configure CI with GitHub Actions (PHPUnit, PHP-CS-Fixer, PHPStan)
- [x] 10.4 Prepare for Packagist submission (verify composer.json metadata, license)
