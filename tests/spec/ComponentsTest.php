<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Components;

/**
 * @covers \cebe\openapi\spec\Components
 */
class ComponentsTest extends \PHPUnit\Framework\TestCase
{
  public function testRead()
  {
    /** @var $components Components */
    $components = Reader::readFromYaml(
      <<<'YAML'
schemas:
  GeneralError:
    type: object
    properties:
      code:
        type: integer
        format: int32
      message:
        type: string
  Category:
    type: object
    properties:
      id:
        type: integer
        format: int64
      name:
        type: string
  Tag:
    type: object
    properties:
      id:
        type: integer
        format: int64
      name:
        type: string
parameters:
  skipParam:
    name: skip
    in: query
    description: number of items to skip
    required: true
    schema:
      type: integer
      format: int32
  limitParam:
    name: limit
    in: query
    description: max records to return
    required: true
    schema:
      type: integer
      format: int32
responses:
  NotFound:
    description: Entity not found.
  IllegalInput:
    description: Illegal input for operation.
  GeneralError:
    description: General Error
    content:
      application/json:
        schema:
          $ref: '#/components/schemas/GeneralError'
securitySchemes:
  api_key:
    type: apiKey
    name: api_key
    in: header
  petstore_auth:
    type: oauth2
    flows: 
      implicit:
        authorizationUrl: http://example.org/api/oauth/dialog
        scopes:
          write:pets: modify pets in your account
          read:pets: read your pets
YAML,
      Components::class
    );

    $result = $components->validate();
    $this->assertEquals([], $components->getErrors());
    $this->assertTrue($result);

    $this->assertAllInstanceOf(\cebe\openapi\spec\Schema::class, $components->schemas);
    $this->assertCount(3, $components->schemas);
    $this->assertArrayHasKey('GeneralError', $components->schemas);
    $this->assertArrayHasKey('Category', $components->schemas);
    $this->assertArrayHasKey('Tag', $components->schemas);
    $this->assertAllInstanceOf(\cebe\openapi\spec\Response::class, $components->responses);
    $this->assertCount(3, $components->responses);
    $this->assertArrayHasKey('NotFound', $components->responses);
    $this->assertArrayHasKey('IllegalInput', $components->responses);
    $this->assertArrayHasKey('GeneralError', $components->responses);
    $this->assertAllInstanceOf(\cebe\openapi\spec\Parameter::class, $components->parameters);
    $this->assertCount(2, $components->parameters);
    $this->assertArrayHasKey('skipParam', $components->parameters);
    $this->assertArrayHasKey('limitParam', $components->parameters);
    $this->assertAllInstanceOf(\cebe\openapi\spec\Example::class, $components->examples);
    $this->assertCount(0, $components->examples); // TODO
    $this->assertAllInstanceOf(\cebe\openapi\spec\RequestBody::class, $components->requestBodies);
    $this->assertCount(0, $components->requestBodies); // TODO
    $this->assertAllInstanceOf(\cebe\openapi\spec\Header::class, $components->headers);
    $this->assertCount(0, $components->headers); // TODO
    $this->assertAllInstanceOf(\cebe\openapi\spec\SecurityScheme::class, $components->securitySchemes);
    $this->assertCount(2, $components->securitySchemes);
    $this->assertArrayHasKey('api_key', $components->securitySchemes);
    $this->assertArrayHasKey('petstore_auth', $components->securitySchemes);
    $this->assertAllInstanceOf(\cebe\openapi\spec\Link::class, $components->links);
    $this->assertCount(0, $components->links); // TODO
    $this->assertAllInstanceOf(\cebe\openapi\spec\Callback::class, $components->callbacks);
    $this->assertCount(0, $components->callbacks); // TODO
  }

  public function assertAllInstanceOf($className, $array)
  {
    foreach ($array as $k => $v) {
      $this->assertInstanceOf($className, $v, "Asserting that item with key '$k' is instance of $className");
    }
  }

  /**
   * Test Delete a named schema
   */
  public function testDeleteSchema()
  {
    $components = $this->componentData();

    $this->assertEquals(count($components->schemas), 3);
    $this->assertTrue(!empty($components->schemas['GeneralError']));

    $components->removeSchema('GeneralError');
    $this->assertEquals(count($components->schemas), 2);
    $this->assertTrue(empty($components->schemas['GeneralError']));    

    $components->removeSchema('Tag');
    $this->assertEquals(count($components->schemas), 1);
    $this->assertTrue(empty($components->schemas['Tag']));        
  }


  /**
   * Test Delete a named parameter
   */
  public function testDeleteParameter()
  {
    $components = $this->componentData();

    $this->assertEquals(count($components->parameters), 2);
    $this->assertTrue(!empty($components->parameters['skipParam']));

    $components->removeParameter('skipParam');
    $this->assertEquals(count($components->parameters), 1);
    $this->assertTrue(empty($components->parameters['skipParam']));    

    $components->removeParameter('limitParam');
    $this->assertEquals(count($components->parameters), 0);
    $this->assertTrue(empty($components->parameters['limitParam']));        
  } 
  
  /**
   * Test Delete a named response
   */
  public function testDeleteResponse()
  {
    $components = $this->componentData();

    $this->assertEquals(count($components->responses), 3);
    $this->assertTrue(!empty($components->responses['NotFound']));

    $components->removeResponse('NotFound');
    $this->assertEquals(count($components->responses), 2);
    $this->assertTrue(empty($components->responses['NotFound']));    

    $components->removeResponse('GeneralError');
    $this->assertEquals(count($components->responses), 1);
    $this->assertTrue(empty($components->responses['GeneralError']));        
  }  

  /**
   * Test Delete a named request body
   */
  public function testDeleteRequestBodies()
  {
    $components = $this->componentData();

    $this->assertEquals(count($components->requestBodies), 3);
    $this->assertTrue(!empty($components->requestBodies['req1']));

    $components->removeRequestBody('req1');
    $this->assertEquals(count($components->requestBodies), 2);
    $this->assertTrue(empty($components->requestBodies['req1']));    

    $components->removeRequestBody('req2');
    $this->assertEquals(count($components->requestBodies), 1);
    $this->assertTrue(empty($components->requestBodies['req2']));        
  }    
  
  /**
   * Test Delete a named security scheme
   */
  public function testDeletesecuritySchemes()
  {
    $components = $this->componentData();

    $this->assertEquals(count($components->securitySchemes), 2);
    $this->assertTrue(!empty($components->securitySchemes['api_key']));

    $components->removeSecuityScheme('api_key');
    $this->assertEquals(count($components->securitySchemes), 1);
    $this->assertTrue(empty($components->securitySchemes['api_key']));    

  }      

  protected function componentData(): Components
  {
    $components = Reader::readFromYaml(
      <<<'YAML'
      schemas:
        GeneralError:
          type: object
          properties:
            code:
              type: integer
              format: int32
            message:
              type: string
        Category:
          type: object
          properties:
            id:
              type: integer
              format: int64
            name:
              type: string
        Tag:
          type: object
          properties:
            id:
              type: integer
              format: int64
            name:
              type: string
      parameters:
        skipParam:
          name: skip
          in: query
          description: number of items to skip
          required: true
          schema:
            type: integer
            format: int32
        limitParam:
          name: limit
          in: query
          description: max records to return
          required: true
          schema:
            type: integer
            format: int32
      responses:
        NotFound:
          description: Entity not found.
        IllegalInput:
          description: Illegal input for operation.
        GeneralError:
          description: General Error
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/GeneralError'
      securitySchemes:
        api_key:
          type: apiKey
          name: api_key
          in: header
        petstore_auth:
          type: oauth2
          flows: 
            implicit:
              authorizationUrl: http://example.org/api/oauth/dialog
              scopes:
                write:pets: modify pets in your account
                read:pets: read your pets
      requestBodies:
        req1:
          description: 'Request one'
          required: true
          content:
            application/vnd.api+json:
              schema:
                type: object
                properties:
                  data:
                    type: array          

        req2:
          description: 'Request two'
          required: true
          content:
            application/vnd.api+json:
              schema:
                type: object
                properties:
                  data:
                    type: array             

        req3:
          description: 'Request three'
          required: true
          content:
            application/vnd.api+json:
              schema:
                type: object
      YAML,
      Components::class
    );

    $result = $components->validate();
    $this->assertEquals([], $components->getErrors());
    $this->assertTrue($result);

    return $components;
  }
}
