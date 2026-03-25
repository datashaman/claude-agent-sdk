## Why

The Claude Agent SDK currently exists for TypeScript and Python, but there is no official PHP equivalent. PHP powers a significant portion of the web (WordPress, Laravel, Symfony ecosystems), and PHP developers currently have no idiomatic way to build autonomous agents using Claude. A PHP SDK would unlock the Agent SDK for this large developer community using familiar patterns and conventions.

## What Changes

- Create a new PHP package (`datashaman/claude-agent-sdk`) that mirrors the capabilities of the TypeScript and Python SDKs
- Implement the `query()` function for one-off agent interactions with streaming support
- Implement a `ClaudeAgentClient` class for continuous conversations and session management
- Implement custom tool definition using PHP attributes and type declarations
- Implement MCP (Model Context Protocol) server integration
- Support all permission modes (acceptEdits, blockEdits, etc.)
- Support extended thinking configuration
- Support subagent delegation
- Provide session management (list, retrieve, resume sessions)
- Distribute via Composer (Packagist)

## Capabilities

### New Capabilities
- `agent-query`: Core agent query functionality - streaming responses, message handling, and the `query()` entry point
- `tool-definition`: Custom tool definition using PHP attributes, type reflection, and schema generation
- `session-management`: Session persistence - listing, retrieving, and resuming agent sessions
- `mcp-integration`: Model Context Protocol server creation and tool registration
- `permission-modes`: Permission configuration controlling agent capabilities (file edits, command execution, etc.)
- `configuration`: Agent options, environment setup, model selection, and extended thinking configuration

### Modified Capabilities
<!-- No existing capabilities to modify - this is a greenfield project -->

## Impact

- **New package**: `datashaman/claude-agent-sdk` distributed via Composer/Packagist
- **Dependencies**: Requires PHP 8.2+ (for enums, readonly classes, readonly properties, intersection types, DNF types), and a process control mechanism for Claude CLI interaction
- **APIs**: Mirrors the public API surface of the TypeScript/Python SDKs adapted to PHP idioms (e.g., attributes instead of decorators, generators for streaming)
- **Systems**: Requires Claude CLI (`claude`) installed on the host system, same as the other SDKs
