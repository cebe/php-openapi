<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Parameter;

/**
 * @covers \cebe\openapi\spec\Parameter
 */
class ParameterTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $parameter Parameter */
        $parameter = Reader::readFromYaml(<<<'YAML'
name: token
in: header
description: token to be passed as a header
required: true
schema:
  type: array
  items:
    type: integer
    format: int64
style: simple
YAML
            , Parameter::class);

        $result = $parameter->validate();
        $this->assertEquals([], $parameter->getErrors());
        $this->assertTrue($result);

        $this->assertEquals('token', $parameter->name);
        $this->assertEquals('header', $parameter->in);
        $this->assertEquals('token to be passed as a header', $parameter->description);
        $this->assertTrue($parameter->required);

        $this->assertInstanceOf(\cebe\openapi\spec\Schema::class, $parameter->schema);
        $this->assertEquals('array', $parameter->schema->type);

        $this->assertEquals('simple', $parameter->style);

        /** @var $parameter Parameter */
        $parameter = Reader::readFromYaml(<<<'YAML'
in: query
name: coordinates
content:
  application/json:
    schema:
      type: object
      required:
        - lat
        - long
      properties:
        lat:
          type: number
        long:
          type: number
YAML
            , Parameter::class);

        $result = $parameter->validate();
        $this->assertEquals([], $parameter->getErrors());
        $this->assertTrue($result);

        $this->assertEquals('coordinates', $parameter->name);
        $this->assertEquals('query', $parameter->in);
        $this->assertFalse($parameter->required);

        $this->assertInstanceOf(\cebe\openapi\spec\MediaType::class, $parameter->content['application/json']);
        $this->assertInstanceOf(\cebe\openapi\spec\Schema::class, $parameter->content['application/json']->schema);
    }
}