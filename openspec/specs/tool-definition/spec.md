## ADDED Requirements

### Requirement: Tool attribute for function decoration
The SDK SHALL provide a `#[Tool]` PHP attribute that marks a callable as an agent tool, with `name` and `description` properties.

#### Scenario: Define a tool with attribute
- **WHEN** a function is annotated with `#[Tool(name: 'search', description: 'Search the web')]`
- **THEN** the SDK registers it as an available tool with the specified name and description

#### Scenario: Tool name defaults to function name
- **WHEN** a function is annotated with `#[Tool(description: 'Does something')]` without an explicit name
- **THEN** the SDK uses the function name (converted to snake_case) as the tool name

### Requirement: Parameter attribute for input schema
The SDK SHALL provide a `#[Parameter]` attribute for function parameters that specifies description, type overrides, and enum constraints.

#### Scenario: Parameter with description and enum
- **WHEN** a parameter is annotated with `#[Parameter(description: 'Unit', enum: ['celsius', 'fahrenheit'])]`
- **THEN** the generated JSON schema includes the description and enum constraint

#### Scenario: Type inference from PHP types
- **WHEN** a parameter has a PHP type declaration (string, int, float, bool, array) but no explicit type in the attribute
- **THEN** the SDK infers the JSON schema type from the PHP type declaration

### Requirement: JSON Schema generation from tool definition
The SDK SHALL generate a valid JSON Schema object for each registered tool, derived from its attributes and type declarations.

#### Scenario: Complete schema generation
- **WHEN** a tool has multiple parameters with types, descriptions, and defaults
- **THEN** the SDK generates a JSON Schema with `type: "object"`, `properties` for each parameter, and `required` listing parameters without defaults

### Requirement: Tool execution callback
The SDK SHALL execute the registered callable when the agent invokes the tool, passing deserialized arguments and returning the serialized result.

#### Scenario: Agent invokes a tool
- **WHEN** the agent sends a tool_use message for a registered tool with JSON arguments
- **THEN** the SDK deserializes the arguments, calls the function, and sends the result back to the CLI process

#### Scenario: Tool execution error
- **WHEN** the tool callable throws an exception
- **THEN** the SDK catches the exception and returns an error result to the agent with the exception message
