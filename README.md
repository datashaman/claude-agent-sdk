# Claude Agent SDK for PHP

PHP SDK for building autonomous agents powered by Claude. This is the PHP equivalent of the official [TypeScript](https://github.com/anthropics/claude-agent-sdk-typescript) and [Python](https://github.com/anthropics/claude-agent-sdk-python) SDKs.

## Requirements

- PHP 8.2+
- [Claude CLI](https://docs.anthropic.com/en/docs/claude-code/overview) installed and authenticated

## Installation

```bash
composer require datashaman/claude-agent-sdk
```

## Quick Start

### Basic Query

```php
use DataShaman\Claude\AgentSdk\Claude;

foreach (Claude::query('What is PHP?') as $message) {
    if ($message->type === 'content_block_delta' && isset($message->delta['text'])) {
        echo $message->delta['text'];
    }
}
```

### Query with Options

```php
use DataShaman\Claude\AgentSdk\Claude;
use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;
use DataShaman\Claude\AgentSdk\Enum\PermissionMode;

$options = ClaudeAgentOptions::create()
    ->model('claude-sonnet-4-6')
    ->maxTurns(5)
    ->systemPrompt('You are a helpful PHP expert.')
    ->permissionMode(PermissionMode::AcceptEdits);

foreach (Claude::query('Explain generators', $options) as $message) {
    // Process streaming messages
}
```

### Custom Tools

Define tools using PHP attributes:

```php
use DataShaman\Claude\AgentSdk\Attribute\Tool;
use DataShaman\Claude\AgentSdk\Attribute\Parameter;
use DataShaman\Claude\AgentSdk\Claude;
use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;

#[Tool(name: 'get_weather', description: 'Get current weather for a city')]
function getWeather(
    #[Parameter(description: 'City name')]
    string $city,
    #[Parameter(description: 'Temperature unit', enum: ['celsius', 'fahrenheit'])]
    string $unit = 'celsius',
): array {
    // Your weather API logic here
    return ['temp' => 22, 'unit' => $unit, 'city' => $city];
}

$options = ClaudeAgentOptions::create()
    ->tools(['getWeather']);

foreach (Claude::query('What is the weather in London?', $options) as $message) {
    // Tool calls are handled automatically
}
```

### Session Management

```php
use DataShaman\Claude\AgentSdk\ClaudeAgentClient;
use DataShaman\Claude\AgentSdk\ClaudeAgentOptions;

$client = ClaudeAgentClient::create(
    ClaudeAgentOptions::create()->model('claude-sonnet-4-6')
);

// First message
foreach ($client->send('Hello!') as $message) {
    // Process response
}

// Continue the conversation (same session)
foreach ($client->send('Tell me more') as $message) {
    // Process response
}

// List all sessions
$sessions = $client->listSessions();

// Get messages from a session
$messages = $client->getSessionMessages($sessionId);
```

### MCP Server

Create an MCP server that exposes tools to Claude:

```php
use function DataShaman\Claude\AgentSdk\Mcp\createSdkMcpServer;

$server = createSdkMcpServer([
    'getWeather', // Pass tool callables
]);

$server->run(); // Starts listening on stdio
```

Connect to external MCP servers:

```php
$options = ClaudeAgentOptions::create()
    ->mcpServers([
        'myserver' => [
            'command' => 'node',
            'args' => ['path/to/server.js'],
        ],
    ]);
```

## API Reference

### `Claude::query(string $prompt, ?ClaudeAgentOptions $options = null): Generator<Message>`

One-off query that returns a Generator yielding `Message` objects as they stream from the CLI.

### `ClaudeAgentOptions`

Immutable configuration object with fluent builder:

| Method | Description |
|--------|-------------|
| `model(string)` | Claude model to use |
| `maxTurns(int)` | Maximum agent turns |
| `systemPrompt(string)` | Replace system prompt |
| `appendSystemPrompt(string)` | Append to system prompt |
| `tools(array)` | Custom tool callables |
| `mcpServers(array)` | MCP server configurations |
| `permissionMode(PermissionMode)` | Permission mode |
| `allowedTools(array)` | Restrict available tools |
| `cwd(string)` | Working directory for CLI |
| `env(array)` | Environment variables |
| `sessionId(string)` | Resume a session |
| `extendedThinking(array)` | Extended thinking config |
| `permissionPromptHandler(callable)` | Permission callback |

### `ClaudeAgentClient`

Stateful client for multi-turn conversations:

| Method | Description |
|--------|-------------|
| `send(string)` | Send a message, returns Generator |
| `getSessionId()` | Current session ID |
| `listSessions()` | List all sessions |
| `getSessionMessages(string)` | Get session history |

### `Message`

Readonly DTO for streaming events:

| Property | Type | Description |
|----------|------|-------------|
| `type` | `string` | Event type (message_start, content_block_delta, etc.) |
| `index` | `?int` | Content block index |
| `message` | `?array` | Full message (for message_start) |
| `contentBlock` | `?array` | Content block data |
| `delta` | `?array` | Delta data for streaming |
| `sessionId` | `string` | Session ID |
| `uuid` | `string` | Event UUID |

### Permission Modes

```php
PermissionMode::Default           // Default CLI behavior
PermissionMode::AcceptEdits       // Auto-accept file edits
PermissionMode::BlockEdits        // Block all file edits
PermissionMode::BypassPermissions // Skip all permission prompts
```

## License

MIT
