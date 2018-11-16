<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Reference;
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

        /** @var $response \cebe\openapi\spec\Response */
        $response = $openapi->paths->getPath('/pet')->get->responses['200'];
        $this->assertInstanceOf(Reference::class, $response->content['application/json']->schema);
        $this->assertInstanceOf(Reference::class, $response->content['application/json']->examples['frog']);

        $openapi->resolveReferences(new \cebe\openapi\ReferenceContext($openapi, 'file:///tmp/openapi.yaml'));

        $this->assertInstanceOf(Schema::class, $refSchema = $response->content['application/json']->schema);
        $this->assertInstanceOf(Example::class, $refExample = $response->content['application/json']->examples['frog']);

        $this->assertSame($openapi->components->schemas['Pet'], $refSchema);
        $this->assertSame($openapi->components->examples['frog-example'], $refExample);
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

        /** @var $response \cebe\openapi\spec\Response */
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

}
