<?php

use cebe\openapi\spec\Components;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\SecurityRequirement;
use cebe\openapi\spec\SecurityRequirements;
use cebe\openapi\spec\SecurityScheme;

class WriterTest extends \PHPUnit\Framework\TestCase
{
    private function createOpenAPI($merge = [])
    {
        return new \cebe\openapi\spec\OpenApi(array_merge([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
            ],
            'paths' => [],
        ], $merge));
    }

    public function testWriteJson()
    {
        $openapi = $this->createOpenAPI();

        $json = \cebe\openapi\Writer::writeToJson($openapi);

        $this->assertEquals(preg_replace('~\R~', "\n", <<<JSON
{
    "openapi": "3.0.0",
    "info": {
        "title": "Test API",
        "version": "1.0.0"
    },
    "paths": {}
}
JSON
),
            $json
        );
    }

    public function testWriteJsonMofify()
    {
        $openapi = $this->createOpenAPI();

        $openapi->paths['/test'] = new \cebe\openapi\spec\PathItem([
            'description' => 'something'
        ]);

        $json = \cebe\openapi\Writer::writeToJson($openapi);

        $this->assertEquals(preg_replace('~\R~', "\n", <<<JSON
{
    "openapi": "3.0.0",
    "info": {
        "title": "Test API",
        "version": "1.0.0"
    },
    "paths": {
        "\/test": {
            "description": "something"
        }
    }
}
JSON
),
            $json
        );
    }

    public function testWriteYaml()
    {
        $openapi = $this->createOpenAPI();

        $yaml = \cebe\openapi\Writer::writeToYaml($openapi);


        $this->assertEquals(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'Test API'
  version: 1.0.0
paths: {  }

YAML
        ),
            $yaml
        );
    }

    public function testWriteEmptySecurityJson()
    {
        $openapi = $this->createOpenAPI([
            'security' => [],
        ]);

        $json = \cebe\openapi\Writer::writeToJson($openapi);

        $this->assertEquals(preg_replace('~\R~', "\n", <<<JSON
{
    "openapi": "3.0.0",
    "info": {
        "title": "Test API",
        "version": "1.0.0"
    },
    "paths": {},
    "security": []
}
JSON
        ),
            $json
        );
    }


    public function testWriteEmptySecurityYaml()
    {
        $openapi = $this->createOpenAPI([
            'security' => [],
        ]);

        $yaml = \cebe\openapi\Writer::writeToYaml($openapi);


        $this->assertEquals(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'Test API'
  version: 1.0.0
paths: {  }
security: []

YAML
        ),
            $yaml
        );
    }

    public function testWriteEmptySecurityPartJson()
    {
        $openapi = $this->createOpenAPI([
            'security' => new SecurityRequirements([
                'Bearer' => new SecurityRequirement([])
            ]),
        ]);

        $json = \cebe\openapi\Writer::writeToJson($openapi);

        $this->assertEquals(preg_replace('~\R~', "\n", <<<JSON
{
    "openapi": "3.0.0",
    "info": {
        "title": "Test API",
        "version": "1.0.0"
    },
    "paths": {},
    "security": [
        {
            "Bearer": []
        }
    ]
}
JSON
        ),
            $json
        );
    }


    public function testWriteEmptySecurityPartYaml()
    {
        $openapi = $this->createOpenAPI([
            'security' => new SecurityRequirements([
                'Bearer' => new SecurityRequirement([])
            ]),
        ]);

        $yaml = \cebe\openapi\Writer::writeToYaml($openapi);


        $this->assertEquals(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'Test API'
  version: 1.0.0
paths: {  }
security:
  -
    Bearer: []

YAML
        ),
            $yaml
        );
    }

    public function testSecurityAtPathOperationLevel()
    {
        $openapi = $this->createOpenAPI([
            'components' => new Components([
                'securitySchemes' => [
                    'BearerAuth' => new SecurityScheme([
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'AuthToken and JWT Format' # optional, arbitrary value for documentation purposes
                    ]),
                ],
            ]),
            'paths' => [
                '/test' => new PathItem([
                    'get' => new Operation([
                        'security' => new SecurityRequirements([
                            'BearerAuth' => new SecurityRequirement([]),
                        ]),
                        'responses' => new Responses([
                            200 => new Response(['description' => 'OK']),
                        ])
                    ])
                ])
            ]
        ]);

        $yaml = \cebe\openapi\Writer::writeToYaml($openapi);


        $this->assertEquals(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'Test API'
  version: 1.0.0
paths:
  /test:
    get:
      responses:
        '200':
          description: OK
      security:
        -
          BearerAuth: []
components:
  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: 'AuthToken and JWT Format'

YAML
        ),
            $yaml
        );
    }

    public function testSecurityAtGlobalLevel()
    {
        $openapi = $this->createOpenAPI([
            'components' => new Components([
                'securitySchemes' => [
                    'BearerAuth' => new SecurityScheme([
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'AuthToken and JWT Format' # optional, arbitrary value for documentation purposes
                    ])
                ],
            ]),
            'security' => new SecurityRequirements([
                'BearerAuth' => new SecurityRequirement([])
            ]),
            'paths' => [],
        ]);

        $yaml = \cebe\openapi\Writer::writeToYaml($openapi);


        $this->assertEquals(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'Test API'
  version: 1.0.0
paths: {  }
components:
  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: 'AuthToken and JWT Format'
security:
  -
    BearerAuth: []

YAML
        ),
            $yaml
        );
    }
}
