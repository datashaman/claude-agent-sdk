## ADDED Requirements

### Requirement: List sessions
The SDK SHALL provide a `listSessions()` method that returns an array of session metadata objects from the Claude CLI.

#### Scenario: List all sessions
- **WHEN** the developer calls `$client->listSessions()`
- **THEN** the SDK executes `claude sessions list --json` and returns an array of session objects with id, title, and timestamps

#### Scenario: No sessions exist
- **WHEN** the developer calls `listSessions()` and no sessions exist
- **THEN** the SDK returns an empty array

### Requirement: Get session messages
The SDK SHALL provide a `getSessionMessages(string $sessionId)` method that returns the full message history for a session.

#### Scenario: Retrieve session messages
- **WHEN** the developer calls `$client->getSessionMessages('session-123')`
- **THEN** the SDK returns an array of `Message` objects representing the full conversation history

#### Scenario: Invalid session ID
- **WHEN** the developer calls `getSessionMessages()` with a non-existent session ID
- **THEN** the SDK throws a `SessionNotFoundException`

### Requirement: Resume session
The SDK SHALL support resuming a previous session by accepting a session ID in `ClaudeAgentOptions`.

#### Scenario: Resume an existing session
- **WHEN** the developer passes `sessionId` in the options and calls `query()`
- **THEN** the SDK passes `--resume <sessionId>` to the CLI, continuing the previous conversation

### Requirement: ClaudeAgentClient for stateful sessions
The SDK SHALL provide a `ClaudeAgentClient` class that maintains session state across multiple queries.

#### Scenario: Multi-turn conversation with client
- **WHEN** the developer creates a `ClaudeAgentClient` and calls `send()` multiple times
- **THEN** each call continues the same session, preserving conversation context
