## ADDED Requirements

### Requirement: Create SDK MCP server
The SDK SHALL provide a `createSdkMcpServer()` function that creates an MCP server exposing registered tools to Claude via the Model Context Protocol.

#### Scenario: Create MCP server with tools
- **WHEN** the developer calls `createSdkMcpServer($tools)` with an array of tool definitions
- **THEN** the SDK creates an MCP server that exposes those tools over stdio transport

### Requirement: MCP server configuration in options
The SDK SHALL support configuring MCP servers in `ClaudeAgentOptions` so the agent can connect to external MCP servers.

#### Scenario: Connect to external MCP server
- **WHEN** the developer passes `mcpServers` configuration in options with server name and command
- **THEN** the SDK passes the MCP server configuration to the CLI process

#### Scenario: Multiple MCP servers
- **WHEN** the developer configures multiple MCP servers in options
- **THEN** all servers are available to the agent during the session

### Requirement: MCP tool registration
The SDK SHALL allow registering PHP callables as MCP tools with the same `#[Tool]` attribute used for direct tools.

#### Scenario: Register tool on MCP server
- **WHEN** a `#[Tool]`-annotated function is passed to `createSdkMcpServer()`
- **THEN** the MCP server exposes it with the correct JSON Schema and handles invocations
