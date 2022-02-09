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

    public function testNullable()
    {
        /** @var $schema Schema */
        $schema = Reader::readFromJson('{"type": "string"}', Schema::class);
        $this->assertEquals(Type::STRING, $schema->type);
        $this->assertFalse($schema->nullable);

        /** @var $schema Schema */
        $schema = Reader::readFromJson('{"type": "string", "nullable": false}', Schema::class);
        $this->assertEquals(Type::STRING, $schema->type);
        $this->assertFalse($schema->nullable);

        /** @var $schema Schema */
        $schema = Reader::readFromJson('{"type": "string", "nullable": true}', Schema::class);
        $this->assertEquals(Type::STRING, $schema->type);
        $this->assertTrue($schema->nullable);

        // nullable is undefined if no type is given
        $schema = Reader::readFromJson('{"oneOf": [{"type": "string"}, {"type": "integer"}]}', Schema::class);
        $this->assertNull($schema->type);
        $this->assertNull($schema->nullable);
    }

    public function testMinMax()
    {
        /** @var $schema Schema */
        $schema = Reader::readFromJson('{"type": "integer"}', Schema::class);
        $this->assertNull($schema->minimum);
        $this->assertNull($schema->exclusiveMinimum);
        $this->assertNull($schema->maximum);
        $this->assertNull($schema->exclusiveMaximum);

        /** @var $schema Schema */
        $schema = Reader::readFromJson('{"type": "integer", "minimum": 1}', Schema::class);
        $this->assertEquals(1, $schema->minimum);
        $this->assertFalse($schema->exclusiveMinimum);
        $this->assertNull($schema->maximum);
        $this->assertNull($schema->exclusiveMaximum);

        /** @var $schema Schema */
        $schema = Reader::readFromJson('{"type": "integer", "minimum": 1, "exclusiveMinimum": true}', Schema::class);
        $this->assertEquals(1, $schema->minimum);
        $this->assertTrue($schema->exclusiveMinimum);
        $this->assertNull($schema->maximum);
        $this->assertNull($schema->exclusiveMaximum);

        /** @var $schema Schema */
        $schema = Reader::readFromJson('{"type": "integer", "maximum": 10}', Schema::class);
        $this->assertEquals(10, $schema->maximum);
        $this->assertFalse($schema->exclusiveMaximum);
        $this->assertNull($schema->minimum);
        $this->assertNull($schema->exclusiveMinimum);

        /** @var $schema Schema */
        $schema = Reader::readFromJson('{"type": "integer", "maximum": 10, "exclusiveMaximum": true}', Schema::class);
        $this->assertEquals(10, $schema->maximum);
        $this->assertTrue($schema->exclusiveMaximum);
        $this->assertNull($schema->minimum);
        $this->assertNull($schema->exclusiveMinimum);
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
        // exclusiveMinimum Default value is null when no minimum is specified.
        $this->assertNull($schema->exclusiveMinimum);
        // exclusiveMaximum Default value is null when no maximum is specified.
        $this->assertNull($schema->exclusiveMaximum);
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

        yield [['additionalProperties' => 'foo'], 'Schema::$additionalProperties MUST be either boolean or a Schema/Reference object, "string" given'];
        yield [['additionalProperties' => 42], 'Schema::$additionalProperties MUST be either boolean or a Schema/Reference object, "integer" given'];
        yield [['additionalProperties' => new stdClass()], 'Schema::$additionalProperties MUST be either boolean or a Schema/Reference object, "stdClass" given'];
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

    /**
     * Ensure Schema properties are accessable and have default values.
     */
    public function testSchemaProperties()
    {
        $schema = new Schema([]);
        $validProperties = [
            // https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#schema-object
            // The following properties are taken directly from the JSON Schema definition and follow the same specifications:
            'title' => null,
            'multipleOf' => null,
            'maximum' => null,
            'exclusiveMaximum' => null,
            'minimum' => null,
            'exclusiveMinimum' => null,
            'maxLength' => null,
            'minLength' => null,
            'pattern' => null,
            'maxItems' => null,
            'minItems' => null,
            'uniqueItems' => false,
            'maxProperties' => null,
            'minProperties' => null,
            'required' => null, // if set, it should not be an empty array, according to the spec
            'enum' => null, // if it is an array, it means restriction of values
            // The following properties are taken from the JSON Schema definition but their definitions were adjusted to the OpenAPI Specification.
            'type' => null,
            'allOf' => null,
            'oneOf' => null,
            'anyOf' => null,
            'not' => null,
            'items' => null,
            'properties' => [],
            'additionalProperties' => true,
            'description' => null,
            'format' => null,
            'default' => null,
            // Other than the JSON Schema subset fields, the following fields MAY be used for further schema documentation:
            'nullable' => false,
            'readOnly' => false,
            'writeOnly' => false,
            'xml' => null,
            'externalDocs' => null,
            'example' => null,
            'deprecated' => false,
        ];

        foreach($validProperties as $property => $defaultValue) {
            $this->assertEquals($defaultValue, $schema->$property, "testing property '$property'");
        }
    }

    public function testRefAdditionalProperties()
    {
        $json = <<<'JSON'
{
  "components": {
    "schemas": {
      "booleanProperties": {
        "type": "boolean"
      },
      "person": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string"
          }
        },
        "additionalProperties": {"$ref": "#/components/schemas/booleanProperties"}
      }
    }
  }
}
JSON;
        $openApi = Reader::readFromJson($json);
        $this->assertInstanceOf(Schema::class, $booleanProperties = $openApi->components->schemas['booleanProperties']);
        $this->assertInstanceOf(Schema::class, $person = $openApi->components->schemas['person']);

        $this->assertEquals('boolean', $booleanProperties->type);
        $this->assertInstanceOf(Reference::class, $person->additionalProperties);

        $this->assertInstanceOf(Schema::class, $refResolved = $person->additionalProperties->resolve(new ReferenceContext($openApi, 'tmp://openapi.yaml')));

        $this->assertEquals('boolean', $refResolved->type);

        $schema = new Schema(['additionalProperties' => new Reference(['$ref' => '#/here'], Schema::class)]);
        $this->assertInstanceOf(Reference::class, $schema->additionalProperties);
    }

    /**
     * Ensure that a property named "$ref" is not interpreted as a reference.
     * @link https://github.com/OAI/OpenAPI-Specification/issues/2173
     */
    public function testPropertyNameRef()
    {
        $json = <<<'JSON'
{
  "components": {
    "schemas": {
      "person": {
        "type": "object",
        "properties": {
          "name": {
            "type": "string"
          },
          "$ref": {
            "type": "string"
          }
        }
      }
    }
  }
}
JSON;
        $openApi = Reader::readFromJson($json);
        $this->assertInstanceOf(Schema::class, $person = $openApi->components->schemas['person']);

        $this->assertEquals(['name', '$ref'], array_keys($person->properties));
        $this->assertInstanceOf(Schema::class, $person->properties['name']);
        $this->assertInstanceOf(Schema::class, $person->properties['$ref']);
        $this->assertEquals('string', $person->properties['name']->type);
        $this->assertEquals('string', $person->properties['$ref']->type);
    }
}
