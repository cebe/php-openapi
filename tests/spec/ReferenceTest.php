<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Example;

/**
 * @covers \cebe\openapi\spec\Reference
 */
class ReferenceTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveInDocument()
    {
        /** @var $openapi OpenApi */
        $openapi = Reader::readFromYaml(<<<'YAML'
openapi: 3.0.0
info:
  title: test api
  version: 1.0.0
components:
  schemas:
    Pet:
      type: object
      properties:
        id:
          type: integer
  examples:
    frog-example:
      description: a frog
  responses:
    Pet:
      description: returns a pet
paths:
  '/pet':
    get:
      responses:
        200:
          description: return a pet
          content:
            'application/json':
              schema:
                $ref: "#/components/schemas/Pet"
              examples:
                frog:
                  $ref: "#/components/examples/frog-example"
  '/pet/1':
    get:
      responses:
        200:
          $ref: "#/components/responses/Pet"
YAML
            , OpenApi::class);

        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors());
        $this->assertTrue($result);

        /** @var $petResponse Response */
        $petResponse = $openapi->paths->getPath('/pet')->get->responses['200'];
        $this->assertInstanceOf(Reference::class, $petResponse->content['application/json']->schema);
        $this->assertInstanceOf(Reference::class, $petResponse->content['application/json']->examples['frog']);
        $this->assertInstanceOf(Reference::class, $openapi->paths->getPath('/pet/1')->get->responses['200']);

        $openapi->resolveReferences(new \cebe\openapi\ReferenceContext($openapi, 'file:///tmp/openapi.yaml'));

        $this->assertInstanceOf(Schema::class, $refSchema = $petResponse->content['application/json']->schema);
        $this->assertInstanceOf(Example::class, $refExample = $petResponse->content['application/json']->examples['frog']);
        $this->assertInstanceOf(Response::class, $refResponse = $openapi->paths->getPath('/pet/1')->get->responses['200']);

        $this->assertSame($openapi->components->schemas['Pet'], $refSchema);
        $this->assertSame($openapi->components->examples['frog-example'], $refExample);
        $this->assertSame($openapi->components->responses['Pet'], $refResponse);
    }

    public function testResolveCyclicReferenceInDocument()
    {
        /** @var $openapi OpenApi */
        $openapi = Reader::readFromYaml(<<<'YAML'
openapi: 3.0.0
info:
  title: test api
  version: 1.0.0
components:
  schemas:
    Pet:
      type: object
      properties:
        id:
          type: array
          items:
            $ref: "#/components/schemas/Pet"
      example:
        $ref: "#/components/examples/frog-example"
  examples:
    frog-example:
      description: a frog
paths:
  '/pet':
    get:
      responses:
        200:
          description: return a pet
          content:
            'application/json':
              schema:
                $ref: "#/components/schemas/Pet"
              examples:
                frog:
                  $ref: "#/components/examples/frog-example"
YAML
            , OpenApi::class);

        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors());
        $this->assertTrue($result);

        /** @var $response Response */
        $response = $openapi->paths->getPath('/pet')->get->responses['200'];
        $this->assertInstanceOf(Reference::class, $response->content['application/json']->schema);
        $this->assertInstanceOf(Reference::class, $response->content['application/json']->examples['frog']);

//        $this->expectException(\cebe\openapi\exceptions\UnresolvableReferenceException::class);
        $openapi->resolveReferences(new \cebe\openapi\ReferenceContext($openapi, 'file:///tmp/openapi.yaml'));

        $this->assertInstanceOf(Schema::class, $petItems = $openapi->components->schemas['Pet']->properties['id']->items);
        $this->assertInstanceOf(Schema::class, $refSchema = $response->content['application/json']->schema);
        $this->assertInstanceOf(Example::class, $refExample = $response->content['application/json']->examples['frog']);

        $this->assertSame($openapi->components->schemas['Pet'], $petItems);
        $this->assertSame($openapi->components->schemas['Pet'], $refSchema);
        $this->assertSame($openapi->components->examples['frog-example'], $refExample);
    }

    public function testResolveFile()
    {
        $file = __DIR__ . '/data/reference/base.yaml';
        /** @var $openapi OpenApi */
        $openapi = Reader::readFromYaml(str_replace('##ABSOLUTEPATH##', 'file://' . dirname($file), file_get_contents($file)));

        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors());
        $this->assertTrue($result);

        $this->assertInstanceOf(Reference::class, $petItems = $openapi->components->schemas['Pet']);
        $this->assertInstanceOf(Reference::class, $petItems = $openapi->components->schemas['Dog']);

        $openapi->resolveReferences(new \cebe\openapi\ReferenceContext($openapi, $file));

        $this->assertInstanceOf(Schema::class, $petItems = $openapi->components->schemas['Pet']);
        $this->assertInstanceOf(Schema::class, $petItems = $openapi->components->schemas['Dog']);
        $this->assertArrayHasKey('id', $openapi->components->schemas['Pet']->properties);
        $this->assertArrayHasKey('name', $openapi->components->schemas['Dog']->properties);

        // second level reference inside of definitions.yaml
        $this->assertArrayHasKey('food', $openapi->components->schemas['Dog']->properties);
        $this->assertInstanceOf(Reference::class, $openapi->components->schemas['Dog']->properties['food']);

        $openapi->resolveReferences(new \cebe\openapi\ReferenceContext($openapi, $file));

        $this->assertArrayHasKey('food', $openapi->components->schemas['Dog']->properties);
        $this->assertInstanceOf(Schema::class, $openapi->components->schemas['Dog']->properties['food']);
        $this->assertArrayHasKey('id', $openapi->components->schemas['Dog']->properties['food']->properties);
        $this->assertArrayHasKey('name', $openapi->components->schemas['Dog']->properties['food']->properties);
        $this->assertEquals(1, $openapi->components->schemas['Dog']->properties['food']->properties['id']->example);
    }

    public function testResolveFileHttp()
    {
        $file = 'https://raw.githubusercontent.com/cebe/php-openapi/290389bbd337cf4d70ecedfd3a3d886715e19552/tests/spec/data/reference/base.yaml';
        /** @var $openapi OpenApi */
        $openapi = Reader::readFromYaml(str_replace('##ABSOLUTEPATH##', dirname($file), file_get_contents($file)));

        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors());
        $this->assertTrue($result);

        $this->assertInstanceOf(Reference::class, $petItems = $openapi->components->schemas['Pet']);
        $this->assertInstanceOf(Reference::class, $petItems = $openapi->components->schemas['Dog']);

        $openapi->resolveReferences(new \cebe\openapi\ReferenceContext($openapi, $file));

        $this->assertInstanceOf(Schema::class, $petItems = $openapi->components->schemas['Pet']);
        $this->assertInstanceOf(Schema::class, $petItems = $openapi->components->schemas['Dog']);
        $this->assertArrayHasKey('id', $openapi->components->schemas['Pet']->properties);
        $this->assertArrayHasKey('name', $openapi->components->schemas['Dog']->properties);
    }

}
