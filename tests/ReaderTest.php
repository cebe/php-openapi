<?php

/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class ReaderTest extends \PHPUnit\Framework\TestCase
{
    public function testReadJson()
    {
        $openapi = \cebe\openapi\Reader::readFromJson(<<<JSON
{
  "openapi": "3.0.0",
  "info": {
    "title": "Test API",
    "version": "1.0.0"
  },
  "paths": {

  }
}
JSON
        );

        $this->assertApiContent($openapi);
    }

    public function testReadYaml()
    {
        $openapi = \cebe\openapi\Reader::readFromYaml(<<<YAML
openapi: 3.0.0
info:
  title: "Test API"
  version: "1.0.0"
paths:
  /somepath:
YAML
        );

        $this->assertApiContent($openapi);
    }

    private function assertApiContent(\cebe\openapi\spec\OpenApi $openapi)
    {
        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors());
        $this->assertTrue($result);


        $this->assertEquals("3.0.0", $openapi->openapi);
        $this->assertEquals("Test API", $openapi->info->title);
        $this->assertEquals("1.0.0", $openapi->info->version);
    }

    
    // TODO test invalid JSON
    // TODO test invalid YAML
}
