<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk;

use DataShaman\Claude\AgentSdk\Attribute\Parameter as ParameterAttribute;
use DataShaman\Claude\AgentSdk\Attribute\Tool as ToolAttribute;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

final class ToolRegistry
{
    /** @var array<string, array{callable: callable, schema: array}> */
    private array $tools = [];

    public function register(callable $callable): void
    {
        $reflection = $this->getReflection($callable);
        $attributes = $reflection->getAttributes(ToolAttribute::class);

        if (empty($attributes)) {
            throw new \InvalidArgumentException('Callable must have a #[Tool] attribute');
        }

        /** @var ToolAttribute $toolAttr */
        $toolAttr = $attributes[0]->newInstance();
        $name = $toolAttr->name ?? $this->toSnakeCase($reflection->getShortName());
        $schema = $this->generateSchema($reflection, $toolAttr);

        $this->tools[$name] = [
            'callable' => $callable,
            'schema' => $schema,
        ];
    }

    public function has(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    public function getSchema(string $name): array
    {
        return $this->tools[$name]['schema'];
    }

    /** @return array<string, array> */
    public function getAllSchemas(): array
    {
        $schemas = [];
        foreach ($this->tools as $name => $tool) {
            $schemas[$name] = $tool['schema'];
        }
        return $schemas;
    }

    public function execute(string $name, array $arguments): mixed
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException("Tool not found: {$name}");
        }

        return ($this->tools[$name]['callable'])(...$arguments);
    }

    private function getReflection(callable $callable): ReflectionFunction|ReflectionMethod
    {
        if ($callable instanceof \Closure) {
            return new ReflectionFunction($callable);
        }

        if (is_string($callable)) {
            return new ReflectionFunction($callable);
        }

        if (is_array($callable)) {
            return new ReflectionMethod($callable[0], $callable[1]);
        }

        return new ReflectionFunction($callable(...));
    }

    private function generateSchema(ReflectionFunction|ReflectionMethod $reflection, ToolAttribute $toolAttr): array
    {
        $properties = [];
        $required = [];

        foreach ($reflection->getParameters() as $param) {
            $paramSchema = $this->generateParameterSchema($param);
            $properties[$param->getName()] = $paramSchema;

            if (!$param->isOptional()) {
                $required[] = $param->getName();
            }
        }

        $schema = [
            'name' => $toolAttr->name ?? $this->toSnakeCase($reflection->getShortName()),
            'description' => $toolAttr->description,
            'input_schema' => [
                'type' => 'object',
                'properties' => $properties,
            ],
        ];

        if (!empty($required)) {
            $schema['input_schema']['required'] = $required;
        }

        return $schema;
    }

    private function generateParameterSchema(ReflectionParameter $param): array
    {
        $paramAttrs = $param->getAttributes(ParameterAttribute::class);
        $paramAttr = !empty($paramAttrs) ? $paramAttrs[0]->newInstance() : null;

        $schema = [];

        // Type from attribute override or PHP type
        if ($paramAttr?->type !== null) {
            $schema['type'] = $paramAttr->type;
        } else {
            $schema['type'] = $this->phpTypeToJsonType($param->getType());
        }

        if ($paramAttr?->description !== null) {
            $schema['description'] = $paramAttr->description;
        }

        if ($paramAttr?->enum !== null) {
            $schema['enum'] = $paramAttr->enum;
        }

        if ($param->isDefaultValueAvailable()) {
            $schema['default'] = $param->getDefaultValue();
        }

        return $schema;
    }

    private function phpTypeToJsonType(?\ReflectionType $type): string
    {
        if ($type === null || !$type instanceof ReflectionNamedType) {
            return 'string';
        }

        return match ($type->getName()) {
            'int', 'integer' => 'integer',
            'float', 'double' => 'number',
            'bool', 'boolean' => 'boolean',
            'array' => 'array',
            default => 'string',
        };
    }

    private function toSnakeCase(string $name): string
    {
        return strtolower((string) preg_replace('/[A-Z]/', '_$0', lcfirst($name)));
    }
}
