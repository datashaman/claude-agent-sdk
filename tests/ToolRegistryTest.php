<?php

declare(strict_types=1);

namespace DataShaman\Claude\AgentSdk\Tests;

use DataShaman\Claude\AgentSdk\Attribute\Parameter;
use DataShaman\Claude\AgentSdk\Attribute\Tool;
use DataShaman\Claude\AgentSdk\ToolRegistry;
use PHPUnit\Framework\TestCase;

#[Tool(name: 'get_weather', description: 'Get current weather')]
function getWeather(
    #[Parameter(description: 'City name')]
    string $city,
    #[Parameter(description: 'Temperature unit', enum: ['celsius', 'fahrenheit'])]
    string $unit = 'celsius',
): array {
    return ['temp' => 22, 'unit' => $unit, 'city' => $city];
}

#[Tool(description: 'Add two numbers')]
function addNumbers(int $a, int $b): int
{
    return $a + $b;
}

final class ToolRegistryTest extends TestCase
{
    private ToolRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new ToolRegistry();
    }

    public function testRegisterToolWithExplicitName(): void
    {
        $this->registry->register('DataShaman\Claude\AgentSdk\Tests\getWeather');

        $this->assertTrue($this->registry->has('get_weather'));
    }

    public function testRegisterToolInfersSnakeCaseName(): void
    {
        $this->registry->register('DataShaman\Claude\AgentSdk\Tests\addNumbers');

        $this->assertTrue($this->registry->has('add_numbers'));
    }

    public function testSchemaGeneration(): void
    {
        $this->registry->register('DataShaman\Claude\AgentSdk\Tests\getWeather');

        $schema = $this->registry->getSchema('get_weather');

        $this->assertSame('get_weather', $schema['name']);
        $this->assertSame('Get current weather', $schema['description']);
        $this->assertSame('object', $schema['input_schema']['type']);

        $props = $schema['input_schema']['properties'];
        $this->assertSame('string', $props['city']['type']);
        $this->assertSame('City name', $props['city']['description']);
        $this->assertSame('string', $props['unit']['type']);
        $this->assertSame(['celsius', 'fahrenheit'], $props['unit']['enum']);
        $this->assertSame('celsius', $props['unit']['default']);

        $this->assertSame(['city'], $schema['input_schema']['required']);
    }

    public function testSchemaGenerationWithIntTypes(): void
    {
        $this->registry->register('DataShaman\Claude\AgentSdk\Tests\addNumbers');

        $schema = $this->registry->getSchema('add_numbers');
        $props = $schema['input_schema']['properties'];

        $this->assertSame('integer', $props['a']['type']);
        $this->assertSame('integer', $props['b']['type']);
        $this->assertSame(['a', 'b'], $schema['input_schema']['required']);
    }

    public function testExecuteTool(): void
    {
        $this->registry->register('DataShaman\Claude\AgentSdk\Tests\getWeather');

        $result = $this->registry->execute('get_weather', ['city' => 'London']);

        $this->assertSame(['temp' => 22, 'unit' => 'celsius', 'city' => 'London'], $result);
    }

    public function testExecuteToolWithAllArgs(): void
    {
        $this->registry->register('DataShaman\Claude\AgentSdk\Tests\getWeather');

        $result = $this->registry->execute('get_weather', ['city' => 'Berlin', 'unit' => 'fahrenheit']);

        $this->assertSame(['temp' => 22, 'unit' => 'fahrenheit', 'city' => 'Berlin'], $result);
    }

    public function testExecuteUnknownToolThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tool not found: nonexistent');

        $this->registry->execute('nonexistent', []);
    }

    public function testRegisterCallableWithoutAttributeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->registry->register(fn () => null);
    }

    public function testGetAllSchemas(): void
    {
        $this->registry->register('DataShaman\Claude\AgentSdk\Tests\getWeather');
        $this->registry->register('DataShaman\Claude\AgentSdk\Tests\addNumbers');

        $schemas = $this->registry->getAllSchemas();

        $this->assertCount(2, $schemas);
        $this->assertArrayHasKey('get_weather', $schemas);
        $this->assertArrayHasKey('add_numbers', $schemas);
    }
}
