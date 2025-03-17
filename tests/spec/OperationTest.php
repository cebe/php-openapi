<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\ExternalDocumentation;

/**
 * @covers \cebe\openapi\spec\Operation
 * @covers \cebe\openapi\spec\ExternalDocumentation
 */
class OperationTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $operation Operation */
        $operation = Reader::readFromYaml(<<<'YAML'
tags:
- pet
summary: Updates a pet in the store with form data
operationId: updatePetWithForm
parameters:
- name: petId
  in: path
  description: ID of pet that needs to be updated
  required: true
  schema:
    type: string
requestBody:
  content:
    'application/x-www-form-urlencoded':
      schema:
       properties:
          name: 
            description: Updated name of the pet
            type: string
          status:
            description: Updated status of the pet
            type: string
       required:
         - status
responses:
  '200':
    description: Pet updated.
    content: 
      'application/json': {}
      'application/xml': {}
  '405':
    description: Method Not Allowed
    content: 
      'application/json': {}
      'application/xml': {}
security:
- petstore_auth:
  - write:pets
  - read:pets
externalDocs:
  description: Find more info here
  url: https://example.com
YAML
        , Operation::class);

        $result = $operation->validate();
        $this->assertEquals([], $operation->getErrors());
        $this->assertTrue($result);

        $this->assertCount(1, $operation->tags);
        $this->assertEquals(['pet'], $operation->tags);

        $this->assertEquals('Updates a pet in the store with form data', $operation->summary);
        $this->assertEquals('updatePetWithForm', $operation->operationId);

        $this->assertCount(1, $operation->parameters);
        $this->assertInstanceOf(\cebe\openapi\spec\Parameter::class, $operation->parameters[0]);
        $this->assertEquals('petId', $operation->parameters[0]->name);

        $this->assertInstanceOf(\cebe\openapi\spec\RequestBody::class, $operation->requestBody);
        $this->assertCount(1, $operation->requestBody->content);
        $this->assertArrayHasKey('application/x-www-form-urlencoded', $operation->requestBody->content);

        $this->assertInstanceOf(\cebe\openapi\spec\Responses::class, $operation->responses);

        $this->assertCount(1, $operation->security->getRequirements());
        $this->assertInstanceOf(\cebe\openapi\spec\SecurityRequirements::class, $operation->security);
        $this->assertInstanceOf(\cebe\openapi\spec\SecurityRequirement::class, $operation->security->getRequirement('petstore_auth'));
        $this->assertCount(2, $operation->security->getRequirement('petstore_auth')->getSerializableData());
        $this->assertEquals(['write:pets', 'read:pets'], $operation->security->getRequirement('petstore_auth')->getSerializableData());

        $this->assertInstanceOf(ExternalDocumentation::class, $operation->externalDocs);
        $this->assertEquals('Find more info here', $operation->externalDocs->description);
        $this->assertEquals('https://example.com', $operation->externalDocs->url);

        // deprecated Default value is false.
        $this->assertFalse($operation->deprecated);
    }
}
