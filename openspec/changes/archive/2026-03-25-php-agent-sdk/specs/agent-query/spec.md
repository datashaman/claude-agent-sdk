## ADDED Requirements

### Requirement: Query function entry point
The SDK SHALL provide a `query()` function that accepts a prompt string and optional `ClaudeAgentOptions`, spawns the Claude CLI as a subprocess, and returns a Generator that yields message objects.

#### Scenario: Basic query with default options
- **WHEN** the developer calls `query('What is PHP?')`
- **THEN** the SDK spawns `claude` CLI with the prompt and yields message objects as they stream from the process

#### Scenario: Query with custom options
- **WHEN** the developer calls `query('Explain generators', $options)` where `$options` specifies model, max turns, and system prompt
- **THEN** the SDK passes all options as CLI arguments to the spawned process

### Requirement: Streaming message output
The SDK SHALL yield each JSON-line message from the Claude CLI stdout as a typed message object, in the order received.

#### Scenario: Multiple message types in a stream
- **WHEN** the CLI outputs assistant text, tool use, and result messages
- **THEN** the generator yields each as a `Message` object with the correct `type` property and associated content

#### Scenario: Streaming partial content
- **WHEN** the CLI outputs content incrementally
- **THEN** each chunk is yielded as it arrives without waiting for the full response

### Requirement: Process lifecycle management
The SDK SHALL manage the Claude CLI subprocess lifecycle — spawning on query start, monitoring during execution, and cleaning up on completion or error.

#### Scenario: Successful completion
- **WHEN** the CLI process exits with code 0
- **THEN** the generator completes normally and the process resources are released

#### Scenario: CLI process failure
- **WHEN** the CLI process exits with a non-zero exit code
- **THEN** the SDK throws a `ClaudeProcessException` with the exit code and stderr output

#### Scenario: CLI not found
- **WHEN** the `claude` binary is not found on the system PATH
- **THEN** the SDK throws a `ClaudeNotFoundException` with instructions for installation

### Requirement: Conversation input support
The SDK SHALL support sending multi-turn conversations by accepting an array of previous messages alongside the new prompt.

#### Scenario: Continue a conversation
- **WHEN** the developer passes `messages` array with prior conversation history and a new prompt
- **THEN** the SDK sends the full conversation context to the CLI process
