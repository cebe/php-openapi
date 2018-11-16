<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Header;

/**
 * @covers \cebe\openapi\spec\Header
 */
class HeaderTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $header Header */
        $header = Reader::readFromJson(<<<JSON
{
  "description": "The number of allowed requests in the current period",
  "schema": {
    "type": "integer"
  }
}
JSON
        , Header::class);

        $result = $header->validate();
        $this->assertEquals([], $header->getErrors());
        $this->assertTrue($result);

        $this->assertEquals('The number of allowed requests in the current period', $header->description);
        $this->assertInstanceOf(\cebe\openapi\spec\Schema::class, $header->schema);
        $this->assertEquals('integer', $header->schema->type);
    }

}
