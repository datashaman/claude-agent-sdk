## Context

The Claude Agent SDK enables developers to build autonomous agents powered by Claude. Official SDKs exist for TypeScript (`@anthropic-ai/claude-agent-sdk`) and Python (`claude-agent-sdk`). Both work by spawning the Claude CLI (`claude`) as a subprocess and communicating via JSON-line streaming over stdin/stdout.

PHP has no equivalent SDK. This project creates `datashaman/claude-agent-sdk` — a Composer package that provides the same capabilities using idiomatic PHP patterns.

**Key constraint**: The SDK does not call the Anthropic API directly. It wraps the Claude CLI binary, which handles authentication, tool execution, and model interaction. The SDK's job is process management, stream parsing, and developer ergonomics.

## Goals / Non-Goals

**Goals:**
- Feature parity with the TypeScript and Python SDKs (query, tools, sessions, MCP, permissions)
- Idiomatic PHP — attributes for tool definitions, generators for streaming, enums for constants
- PHP 8.2+ compatibility (oldest supported PHP version, enabling modern language features)
- Distribute via Composer/Packagist
- Comprehensive test coverage with PHPUnit

**Non-Goals:**
- Direct Anthropic API client (use the `anthropic-php` package for that)
- Async/event-loop frameworks (ReactPHP, Amphp) — keep it synchronous with generators for streaming
- Laravel/Symfony service provider bundles (these can be community packages later)
- GUI or web interface components
- Backwards compatibility with PHP 8.1 or earlier

## Decisions

### 1. Process communication via `proc_open` + JSON-line streaming

**Decision**: Use `proc_open()` to spawn `claude` CLI and read/write JSON lines via pipes.

**Rationale**: This matches how the TypeScript SDK uses `child_process.spawn()` and the Python SDK uses `subprocess.Popen()`. PHP's `proc_open()` provides the same level of control over stdin/stdout/stderr pipes.

**Alternative considered**: Using `symfony/process` — adds a dependency for marginal benefit. The raw `proc_open()` API is sufficient and keeps the package dependency-free for core functionality.

### 2. Streaming via PHP Generators

**Decision**: The `query()` function returns a `Generator<int, mixed, void>` that yields message objects as they arrive.

**Rationale**: Generators are PHP's idiomatic equivalent to async iterators (TypeScript) and async generators (Python). They provide lazy evaluation and natural streaming without requiring an event loop.

```php
foreach (Claude::query('Explain PHP generators') as $message) {
    if ($message->type === 'assistant') {
        echo $message->content;
    }
}
```

### 3. Tool definition via PHP Attributes

**Decision**: Use PHP 8.0+ attributes (`#[Tool]`, `#[Parameter]`) for tool definition, with reflection-based schema generation.

**Rationale**: Attributes are PHP's equivalent to Python decorators and TypeScript's Zod schemas. They provide a declarative, type-safe way to define tools that can be introspected at runtime.

```php
#[Tool(name: 'get_weather', description: 'Get current weather')]
function getWeather(
    #[Parameter(description: 'City name')]
    string $city,
    #[Parameter(description: 'Temperature unit', enum: ['celsius', 'fahrenheit'])]
    string $unit = 'celsius'
): array {
    return ['temp' => 22, 'unit' => $unit, 'city' => $city];
}
```

**Alternative considered**: Array-based tool definitions (like OpenAI's PHP client) — less ergonomic and more error-prone. Attributes leverage PHP's type system.

### 4. Configuration via a value object

**Decision**: Use a `ClaudeAgentOptions` class with readonly properties and a fluent builder.

**Rationale**: Mirrors the TypeScript options object and Python dataclass. Readonly properties and readonly classes (PHP 8.2) ensure immutability after construction.

```php
$options = ClaudeAgentOptions::create()
    ->model('claude-sonnet-4-6')
    ->maxTurns(5)
    ->permissionMode(PermissionMode::AcceptEdits)
    ->systemPrompt('You are a helpful assistant.')
    ->tools([$getWeather]);
```

### 5. Message types as enums + DTOs

**Decision**: Use PHP 8.1 backed enums for message types and readonly classes for message data.

**Rationale**: Enums provide type safety for message type discrimination. Readonly classes ensure message immutability and self-document the data structure.

### 6. Namespace: `DataShaman\Claude\AgentSdk`

**Decision**: Use `DataShaman\Claude\AgentSdk` as the root namespace.

**Rationale**: Follows PSR-4 autoloading, matches the `datashaman/claude-agent-sdk` Composer package name, and groups Claude-related packages under a common vendor namespace.

## Risks / Trade-offs

- **[CLI dependency]** → The SDK requires `claude` CLI installed on the host. Mitigation: Clear error messages when CLI is not found, version detection, and documented installation instructions.

- **[Synchronous blocking]** → PHP's synchronous model means `query()` blocks the calling thread while streaming. Mitigation: Generator-based streaming allows processing messages as they arrive without buffering the entire response. For web contexts, this can be used with server-sent events or output buffering.

- **[PHP version floor at 8.2]** → Excludes older PHP installations. Mitigation: PHP 8.2 is the oldest supported version (security-only until Dec 2026); 8.1 reached EOL in Dec 2025. The features gained (enums, readonly classes, readonly properties, fibers, intersection types, DNF types) significantly improve the SDK's API.

- **[Schema generation from reflection]** → PHP's type system is less expressive than TypeScript's. Union types and complex generics can't be fully represented. Mitigation: The `#[Parameter]` attribute allows explicit type/enum/description overrides where reflection is insufficient.

- **[Process management edge cases]** → Long-running agent sessions may encounter process timeouts, signal handling, or zombie processes. Mitigation: Implement proper process cleanup in destructors and signal handlers, with configurable timeouts.
