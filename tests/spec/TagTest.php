<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\ExternalDocumentation;
use cebe\openapi\spec\Tag;

/**
 * @covers \cebe\openapi\spec\Tag
 */
class TagTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $tag Tag */
        $tag = Reader::readFromYaml(<<<YAML
name: pet
description: Pets operations
YAML
        , Tag::class);

        $result = $tag->validate();
        $this->assertEquals([], $tag->getErrors());
        $this->assertTrue($result);

        $this->assertEquals('pet', $tag->name);
        $this->assertEquals('Pets operations', $tag->description);
        $this->assertNull($tag->externalDocs);

        /** @var $tag Tag */
        $tag = Reader::readFromYaml(<<<YAML
description: Pets operations
externalDocs:
  url: https://example.com
YAML
        , Tag::class);

        $result = $tag->validate();
        $this->assertEquals(['Tag is missing required property: name'], $tag->getErrors());
        $this->assertFalse($result);

        $this->assertInstanceOf(ExternalDocumentation::class, $tag->externalDocs);
    }
}
