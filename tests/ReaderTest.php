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

    /**
     * @see https://github.com/symfony/symfony/issues/34805
     */
    public function testSymfonyYamlBugHunt()
    {
        $openApiFile = __DIR__ . '/../vendor/oai/openapi-specification/examples/v3.0/uspto.yaml';
        $openapi = \cebe\openapi\Reader::readFromYamlFile($openApiFile);

        $inlineYamlExample = $openapi->paths['/']->get->responses['200']->content['application/json']->example;

        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($inlineYamlExample);
        } else {
            $this->assertInternalType('array', $inlineYamlExample);
        }

        $expectedArray = json_decode(<<<JSON
{
  "total": 2,
  "apis": [
    {
      "apiKey": "oa_citations",
      "apiVersionNumber": "v1",
      "apiUrl": "https://developer.uspto.gov/ds-api/oa_citations/v1/fields",
      "apiDocumentationUrl": "https://developer.uspto.gov/ds-api-docs/index.html?url=https://developer.uspto.gov/ds-api/swagger/docs/oa_citations.json"
    },
    {
      "apiKey": "cancer_moonshot",
      "apiVersionNumber": "v1",
      "apiUrl": "https://developer.uspto.gov/ds-api/cancer_moonshot/v1/fields",
      "apiDocumentationUrl": "https://developer.uspto.gov/ds-api-docs/index.html?url=https://developer.uspto.gov/ds-api/swagger/docs/cancer_moonshot.json"
    }
  ]
}
JSON
        , true);
        $this->assertEquals($expectedArray, $inlineYamlExample);
    }

    public function testGetRawSpecData()
    {
        $spec = <<<YML
openapi: "3.0.0"
info:
  version: 1.0.0
  title: Check storage of raw spec data

paths:
  /:
    get:
      summary: List
      operationId: list
      responses:
        '200':
          description: The information

components:
  schemas:
    User:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
    
    Post:
      type: object
      properties:
        id:
          type: integer
        title:
          type: string
        user:
          \$ref: "#/components/schemas/User"

YML;

        $openapi = \cebe\openapi\Reader::readFromYaml($spec);
        $this->assertSame($openapi->getRawSpecData(), [
            'openapi' => '3.0.0',
            'info' => [
                'version' => '1.0.0',
                'title' => 'Check storage of raw spec data',
            ],
            'paths' => [
                '/' => [
                    'get' => [
                        'summary' => 'List',
                        'operationId' => 'list',
                        'responses' => [
                            '200' => [
                                'description' => 'The information',
                            ]
                        ]
                    ]
                ]
            ],
            'components' => [
                'schemas' => [
                    'User' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                            ],
                            'name' => [
                                'type' => 'string',
                            ]
                        ]
                    ],
                    'Post' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => [
                                'type' => 'integer',
                            ],
                            'title' => [
                                'type' => 'string',
                            ],
                            'user' => [
                                '$ref' => '#/components/schemas/User',
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertSame($openapi->components->schemas['User']->getRawSpecData(), [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'name' => [
                    'type' => 'string',
                ]
            ]
        ]);

        $this->assertSame($openapi->components->schemas['Post']->properties['user']->getRawSpecData(), [
            '$ref' => '#/components/schemas/User',
        ]);

    }


    // TODO test invalid JSON
    // TODO test invalid YAML
}
