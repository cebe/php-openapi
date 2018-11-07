<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;

/**
 * @covers \cebe\openapi\spec\Response
 */
class ResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $response Schema */
        $response = Reader::readFromJson(<<<'JSON'
{
  "description": "A complex object array response",
  "content": {
    "application/json": {
      "schema": {
        "type": "array",
        "items": {
          "$ref": "#/components/schemas/VeryComplexType"
        }
      }
    }
  }
}
JSON
        , Response::class);

        $result = $response->validate();
        $this->assertEquals([], $response->getErrors());
        $this->assertTrue($result);

        $this->assertEquals('A complex object array response', $response->description);
        $this->assertArrayHasKey("application/json", $response->content);
        $this->assertInstanceOf(MediaType::class, $response->content["application/json"]);

        /** @var $response Schema */
        $response = Reader::readFromJson(<<<'JSON'
{
  "content": {
    "application/json": {
      "schema": {
        "type": "array",
        "items": {
          "$ref": "#/components/schemas/VeryComplexType"
        }
      }
    }
  }
}
JSON
        , Response::class);

        $result = $response->validate();
        $this->assertEquals([
            'Missing required property: description',
        ], $response->getErrors());
        $this->assertFalse($result);
    }
}
