## ADDED Requirements

### Requirement: Permission mode enum
The SDK SHALL provide a `PermissionMode` enum with values matching the CLI's permission modes: `Default`, `AcceptEdits`, `BlockEdits`, `BypassPermissions`.

#### Scenario: Set permission mode
- **WHEN** the developer sets `permissionMode` to `PermissionMode::AcceptEdits` in options
- **THEN** the SDK passes `--permission-mode acceptEdits` to the CLI process

### Requirement: Allowed tools configuration
The SDK SHALL support an `allowedTools` option that restricts which tools the agent can use.

#### Scenario: Restrict available tools
- **WHEN** the developer sets `allowedTools` to `['Read', 'Glob']` in options
- **THEN** the SDK passes the allowed tools configuration to the CLI, and the agent can only use those tools

### Requirement: Permission prompt handling
The SDK SHALL support a `permissionPromptHandler` callback that is invoked when the agent requests permission for a blocked action.

#### Scenario: Custom permission handler
- **WHEN** the agent requests permission for a tool use and a `permissionPromptHandler` is configured
- **THEN** the SDK invokes the handler with the permission request details and forwards the response to the CLI
