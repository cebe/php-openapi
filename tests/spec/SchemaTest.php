<?php

use cebe\openapi\Reader;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Discriminator;
use cebe\openapi\spec\Reference;
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

    public function testCreateionFromObjects()
    {
        $schema = new Schema([
            'allOf' => [
                new Schema(['type' => 'integer']),
                new Schema(['type' => 'string']),
            ],
            'additionalProperties' => new Schema([
                'type' => 'object',
            ]),
            'discriminator' => new Discriminator([
                'mapping' => ['A' => 'B'],
            ]),
        ]);

        $this->assertSame('integer', $schema->allOf[0]->type);
        $this->assertSame('string', $schema->allOf[1]->type);
        $this->assertInstanceOf(Schema::class, $schema->additionalProperties);
        $this->assertSame('object', $schema->additionalProperties->type);
        $this->assertSame(['A' => 'B'], $schema->discriminator->mapping);
    }


    public function badSchemaProvider()
    {
        yield [['properties' => ['a' => 'foo']], 'Unable to instantiate cebe\openapi\spec\Schema Object with data \'foo\''];
        yield [['properties' => ['a' => 42]], 'Unable to instantiate cebe\openapi\spec\Schema Object with data \'42\''];
        yield [['properties' => ['a' => false]], 'Unable to instantiate cebe\openapi\spec\Schema Object with data \'\''];
        yield [['properties' => ['a' => new stdClass()]], "Unable to instantiate cebe\openapi\spec\Schema Object with data 'stdClass Object\n(\n)\n'"];

        yield [['additionalProperties' => 'foo'], 'Schema::$additionalProperties MUST be either array, boolean or a Schema object, "string" given'];
        yield [['additionalProperties' => 42], 'Schema::$additionalProperties MUST be either array, boolean or a Schema object, "integer" given'];
        yield [['additionalProperties' => new stdClass()], 'Schema::$additionalProperties MUST be either array, boolean or a Schema object, "stdClass" given'];
        // The last one can be supported in future, but now SpecBaseObjects::__construct() requires array explicitly
    }

    /**
     * @dataProvider badSchemaProvider
     */
    public function testPathsCanNotBeCreatedFromBullshit($config, $expectedException)
    {
        $this->expectException(\cebe\openapi\exceptions\TypeErrorException::class);
        $this->expectExceptionMessage($expectedException);

        new Schema($config);
    }

    public function testAllOf()
    {
        $json = <<<'JSON'
{
  "components": {
    "schemas": {
      "identifier": {
        "type": "object",
        "properties": {
           "id": {"type": "string"}
        }
      },
      "person": {
        "allOf": [
          {"$ref": "#/components/schemas/identifier"},
          {
            "type": "object",
            "properties": {
              "name": {
                "type": "string"
              }
            }
          }
        ]
      }
    }
  }
}
JSON;
        $openApi = Reader::readFromJson($json);
        $this->assertInstanceOf(Schema::class, $identifier = $openApi->components->schemas['identifier']);
        $this->assertInstanceOf(Schema::class, $person = $openApi->components->schemas['person']);

        $this->assertEquals('object', $identifier->type);
        $this->assertTrue(is_array($person->allOf));
        $this->assertCount(2, $person->allOf);

        $this->assertInstanceOf(Reference::class, $person->allOf[0]);
        $this->assertInstanceOf(Schema::class, $refResolved = $person->allOf[0]->resolve(new ReferenceContext($openApi, 'tmp://openapi.yaml')));
        $this->assertInstanceOf(Schema::class, $person->allOf[1]);

        $this->assertEquals('object', $refResolved->type);
        $this->assertEquals('object', $person->allOf[1]->type);

        $this->assertArrayHasKey('id', $refResolved->properties);
        $this->assertArrayHasKey('name', $person->allOf[1]->properties);
    }
}
