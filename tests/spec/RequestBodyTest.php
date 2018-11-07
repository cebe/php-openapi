<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\RequestBody;

/**
 * @covers \cebe\openapi\spec\RequestBody
 */
class RequestBodyTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $requestBody RequestBody */
        $requestBody = Reader::readFromJson(<<<'JSON'
{
  "description": "user to add to the system",
  "content": {
    "application/json": {
      "schema": {
        "$ref": "#/components/schemas/User"
      }
    }
  }
}
JSON
        , RequestBody::class);

        $result = $requestBody->validate();
        $this->assertEquals([], $requestBody->getErrors());
        $this->assertTrue($result);

        $this->assertEquals('user to add to the system', $requestBody->description);
        $this->assertArrayHasKey("application/json", $requestBody->content);
        $this->assertInstanceOf(MediaType::class, $requestBody->content["application/json"]);

        /** @var $response RequestBody */
        $requestBody = Reader::readFromJson(<<<'JSON'
{
  "description": "user to add to the system"
}
JSON
        , RequestBody::class);

        $result = $requestBody->validate();
        $this->assertEquals([
            'Missing required property: content',
        ], $requestBody->getErrors());
        $this->assertFalse($result);
    }
}
