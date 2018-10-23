<?php

use cebe\openapi\Reader;


/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class SchemaTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $schema \cebe\openapi\spec\Schema */
        $schema = Reader::readFromJson(<<<JSON
{
  "type": "string",
  "format": "email"
}
JSON
        , \cebe\openapi\spec\Schema::class);

        $result = $schema->validate();
        $this->assertEquals([], $schema->getErrors());
        $this->assertTrue($result);

        $this->assertEquals("string", $schema->type);
        $this->assertEquals("email", $schema->format);
    }

    public function testReadObject()
    {
        /** @var $schema \cebe\openapi\spec\Schema */
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
        , \cebe\openapi\spec\Schema::class);

        $result = $schema->validate();
        $this->assertEquals([], $schema->getErrors());
        $this->assertTrue($result);

        $this->assertEquals("object", $schema->type);
        $this->assertEquals(['name'], $schema->required);
        $this->assertEquals('integer', $schema->properties['age']->type);
        $this->assertEquals(0, $schema->properties['age']->minimum);
    }

}
