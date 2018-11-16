<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Discriminator;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;

/**
 * @covers \cebe\openapi\spec\Schema
 * @covers \cebe\openapi\spec\Discriminator
 */
class SchemaTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $schema Schema */
        $schema = Reader::readFromJson(<<<JSON
{
  "type": "string",
  "format": "email"
}
JSON
        , Schema::class);

        $result = $schema->validate();
        $this->assertEquals([], $schema->getErrors());
        $this->assertTrue($result);

        $this->assertEquals(Type::STRING, $schema->type);
        $this->assertEquals('email', $schema->format);

        // additionalProperties defaults to true.
        $this->assertTrue($schema->additionalProperties);
        // nullable Default value is false.
        $this->assertFalse($schema->nullable);
        // readOnly Default value is false.
        $this->assertFalse($schema->readOnly);
        // writeOnly Default value is false.
        $this->assertFalse($schema->writeOnly);
        // deprecated Default value is false.
        $this->assertFalse($schema->deprecated);
    }

    public function testReadObject()
    {
        /** @var $schema Schema */
        $schema = Reader::readFromJson(<<<'JSON'
{
  "type": "object",
  "required": [
    "name"
  ],
  "properties": {
    "name": {
      "type": "string"
    },
    "address": {
      "$ref": "#/components/schemas/Address"
    },
    "age": {
      "type": "integer",
      "format": "int32",
      "minimum": 0
    }
  }
}
JSON
        , Schema::class);

        $result = $schema->validate();
        $this->assertEquals([], $schema->getErrors());
        $this->assertTrue($result);

        $this->assertEquals(Type::OBJECT, $schema->type);
        $this->assertEquals(['name'], $schema->required);
        $this->assertEquals(Type::INTEGER, $schema->properties['age']->type);
        $this->assertEquals(0, $schema->properties['age']->minimum);

        // additionalProperties defaults to true.
        $this->assertTrue($schema->additionalProperties);
        // nullable Default value is false.
        $this->assertFalse($schema->nullable);
        // readOnly Default value is false.
        $this->assertFalse($schema->readOnly);
        // writeOnly Default value is false.
        $this->assertFalse($schema->writeOnly);
        // deprecated Default value is false.
        $this->assertFalse($schema->deprecated);
    }

    public function testDiscriminator()
    {
        /** @var $schema Schema */
        $schema = Reader::readFromYaml(<<<'YAML'
oneOf:
  - $ref: '#/components/schemas/Cat'
  - $ref: '#/components/schemas/Dog'
  - $ref: '#/components/schemas/Lizard'
discriminator:
  map:
    cat: Cat
    dog: Dog
YAML
            , Schema::class);

        $result = $schema->validate();
        $this->assertEquals([
            'Discriminator is missing required property: propertyName'
        ], $schema->getErrors());
        $this->assertFalse($result);

        /** @var $schema Schema */
        $schema = Reader::readFromYaml(<<<'YAML'
oneOf:
  - $ref: '#/components/schemas/Cat'
  - $ref: '#/components/schemas/Dog'
  - $ref: '#/components/schemas/Lizard'
discriminator:
  propertyName: type
  mapping:
    cat: Cat
    monster: https://gigantic-server.com/schemas/Monster/schema.json
YAML
            , Schema::class);

        $result = $schema->validate();
        $this->assertEquals([], $schema->getErrors());
        $this->assertTrue($result);

        $this->assertInstanceOf(Discriminator::class, $schema->discriminator);
        $this->assertEquals('type', $schema->discriminator->propertyName);
        $this->assertEquals([
            'cat' => 'Cat',
            'monster' => 'https://gigantic-server.com/schemas/Monster/schema.json',
        ], $schema->discriminator->mapping);
    }
}
