<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Example;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Reference;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \cebe\openapi\spec\MediaType
 * @covers \cebe\openapi\spec\Example
 */
class MediaTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $mediaType MediaType */
        $mediaType = Reader::readFromYaml(<<<'YAML'
schema:
  $ref: "#/components/schemas/Pet"
examples:
  cat:
    summary: An example of a cat
    value:
      name: Fluffy
      petType: Cat
      color: White
      gender: male
      breed: Persian
  dog:
    summary: An example of a dog with a cat's name
    value:
      name: Puma
      petType: Dog
      color: Black
      gender: Female
      breed: Mixed
  frog:
    $ref: "#/components/examples/frog-example"
YAML
            , MediaType::class);

        $result = $mediaType->validate();
        $this->assertEquals([], $mediaType->getErrors());
        $this->assertTrue($result);

        $this->assertInstanceOf(Reference::class, $mediaType->schema);

        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($mediaType->examples);
        } else {
            $this->assertInternalType('array', $mediaType->examples);
        }

        $this->assertCount(3, $mediaType->examples);
        $this->assertArrayHasKey('cat', $mediaType->examples);
        $this->assertArrayHasKey('dog', $mediaType->examples);
        $this->assertArrayHasKey('frog', $mediaType->examples);
        $this->assertInstanceOf(Example::class, $mediaType->examples['cat']);
        $this->assertInstanceOf(Example::class, $mediaType->examples['dog']);
        $this->assertInstanceOf(Reference::class, $mediaType->examples['frog']);

        $this->assertEquals('An example of a cat', $mediaType->examples['cat']->summary);
        $expectedCat = [ // TODO we might actually expect this to be an object of stdClass
            'name' => 'Fluffy',
            'petType' => 'Cat',
            'color' => 'White',
            'gender' => 'male',
            'breed' => 'Persian',
        ];
        $this->assertEquals($expectedCat, $mediaType->examples['cat']->value);

    }

    public function testCreateionFromObjects()
    {
        $mediaType = new MediaType([
            'schema' => new \cebe\openapi\spec\Schema([
                'type' => \cebe\openapi\spec\Type::OBJECT,
                'properties' => [
                    'id' => new \cebe\openapi\spec\Schema(['type' => 'string', 'format' => 'uuid']),
                    'profileImage' => new \cebe\openapi\spec\Schema(['type' => 'string', 'format' => 'binary']),
                ],
            ]),
            'encoding' => [
                'id' => [],
                'profileImage' => new \cebe\openapi\spec\Encoding([
                    'contentType' => 'image/png, image/jpeg',
                    'headers' => [
                        'X-Rate-Limit-Limit' => new \cebe\openapi\spec\Header([
                            'description' => 'The number of allowed requests in the current period',
                            'schema' => new \cebe\openapi\spec\Schema(['type' => 'integer']),
                        ]),
                    ],
                ]),
            ],
        ]);

        // default value should be extracted
        $this->assertEquals('text/plain', $mediaType->encoding['id']->contentType);
        // object should be passed.
        $this->assertInstanceOf(\cebe\openapi\spec\Encoding::class, $mediaType->encoding['profileImage']);
    }

    public function badEncodingProvider()
    {
        yield [['encoding' => ['id' => 'foo']], 'Encoding MUST be either array or Encoding object, "string" given'];
        yield [['encoding' => ['id' => 42]], 'Encoding MUST be either array or Encoding object, "integer" given'];
        yield [['encoding' => ['id' => false]], 'Encoding MUST be either array or Encoding object, "boolean" given'];
        yield [['encoding' => ['id' => new stdClass()]], 'Encoding MUST be either array or Encoding object, "stdClass" given'];
        // The last one can be supported in future, but now SpecBaseObjects::__construct() requires array explicitly
    }

    /**
     * @dataProvider badEncodingProvider
     */
    public function testPathsCanNotBeCreatedFromBullshit($config, $expectedException)
    {
        $this->expectException(\cebe\openapi\exceptions\TypeErrorException::class);
        $this->expectExceptionMessage($expectedException);

        new MediaType($config);
    }

    public function testUnresolvedReferencesInEncoding()
    {
        $yaml = Yaml::parse(<<<'YAML'
openapi: "3.0.0"
info:
  version: 1.0.0
  title: Encoding test
paths:
  /pets:
    post:
      summary: Create a pet
      operationId: createPets
      requestBody:
        content:
          multipart/form-data:
            schema:
              type: object
              properties:
                pet:
                  $ref: '#/components/schemas/Pet'
                petImage:
                  type: string
                  format: binary
            encoding:
              pet:
                contentType: application/json
              petImage:
                contentType: image/*
          application/json:
            schema:
              $ref: '#/components/schemas/Pet'
      responses:
        '201':
          description: Null response
components:
  schemas:
    Pet:
      type: object
      properties:
        name:
          type: string
YAML
);
        $openapi = new OpenApi($yaml);
        $result = $openapi->validate();

        $this->assertEquals([], $openapi->getErrors());
        $this->assertTrue($result);
    }
}
