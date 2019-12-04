<?php

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

    /**
     * Test if reading YAML file with anchors works
     */
    public function testReadYamlWithAnchors()
    {
        $openApiFile = __DIR__ . '/spec/data/traits-mixins.yaml';
        $openapi = \cebe\openapi\Reader::readFromYamlFile($openApiFile);

        $this->assertApiContent($openapi);

        $putOperation = $openapi->paths['/foo']->put;
        $this->assertEquals('create foo', $putOperation->description);
        $this->assertTrue($putOperation->responses->hasResponse('200'));
        $this->assertTrue($putOperation->responses->hasResponse('404'));
        $this->assertTrue($putOperation->responses->hasResponse('428'));
        $this->assertTrue($putOperation->responses->hasResponse('default'));

        $respOk = $putOperation->responses->getResponse('200');
        $this->assertEquals('request succeeded', $respOk->description);
        $this->assertEquals('the request id', $respOk->headers['X-Request-Id']->description);

        $resp404 = $putOperation->responses->getResponse('404');
        $this->assertEquals('resource not found', $resp404->description);
        $this->assertEquals('the request id', $resp404->headers['X-Request-Id']->description);

        $resp428 = $putOperation->responses->getResponse('428');
        $this->assertEquals('resource not found', $resp428->description);
        $this->assertEquals('the request id', $resp428->headers['X-Request-Id']->description);

        $respDefault = $putOperation->responses->getResponse('default');
        $this->assertEquals('resource not found', $respDefault->description);
        $this->assertEquals('the request id', $respDefault->headers['X-Request-Id']->description);

        $foo = $openapi->components->schemas['Foo'];
        $this->assertArrayHasKey('uuid', $foo->properties);
        $this->assertArrayHasKey('name', $foo->properties);
        $this->assertArrayHasKey('id', $foo->properties);
        $this->assertArrayHasKey('description', $foo->properties);
        $this->assertEquals('uuid of the resource', $foo->properties['uuid']->description);
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
