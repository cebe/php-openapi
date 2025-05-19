<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\SecurityRequirements;
use cebe\openapi\Writer;

// https://github.com/cebe/php-openapi/issues/238
class Issue238Test extends \PHPUnit\Framework\TestCase
{
    public function test238AddSupportForEmptySecurityRequirementObjectInSecurityRequirementRead()
    {
        $openapi = Reader::readFromYamlFile(dirname(dirname(__DIR__)).'/data/issue/238/spec.yml');
        $this->assertInstanceOf(\cebe\openapi\SpecObjectInterface::class, $openapi);
        $this->assertInstanceOf(\cebe\openapi\spec\SecurityRequirements::class, $openapi->paths->getPath('/path-secured')->getOperations()['get']->security);
        $this->assertSame(json_decode(json_encode($openapi->paths->getPath('/path-secured')->getOperations()['get']->security->getSerializableData()), true), [[]]);

        $openapiJson = Reader::readFromJson(<<<JSON
        {
  "openapi": "3.0.0",
  "info": {
    "title": "Secured API",
    "version": "1.0.0"
  },
  "paths": {
    "/global-secured": {
      "get": {
        "responses": {
          "200": {
            "description": "OK"
          }
        }
      }
    },
    "/path-secured": {
      "get": {
        "security": [
          {}
        ],
        "responses": {
          "200": {
            "description": "OK"
          }
        }
      }
    }
  },
  "components": {
    "securitySchemes": {
      "ApiKeyAuth": {
        "type": "apiKey",
        "in": "header",
        "name": "X-API-Key"
      },
      "BearerAuth": {
        "type": "http",
        "scheme": "bearer"
      }
    }
  },
  "security": [
    {
      "ApiKeyAuth": []
    }
  ]
}

JSON
);

        $this->assertInstanceOf(\cebe\openapi\SpecObjectInterface::class, $openapiJson);
        $this->assertInstanceOf(\cebe\openapi\spec\SecurityRequirements::class, $openapiJson->paths->getPath('/path-secured')->getOperations()['get']->security);
        $this->assertSame(json_decode(json_encode($openapiJson->paths->getPath('/path-secured')->getOperations()['get']->security->getSerializableData()), true), [[]]);
    }

    public function test238AddSupportForEmptySecurityRequirementObjectInSecurityRequirementWrite()
    {
        $openapi = $this->createOpenAPI([
            'security' => new SecurityRequirements([
                []
            ]),
        ]);

        $yaml = Writer::writeToYaml($openapi);

        $this->assertEquals(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'Test API'
  version: 1.0.0
paths: {  }
security:
  - {  }

YAML
        ),
            $yaml
        );

        $openapiJson = $this->createOpenAPI([
            'security' => new SecurityRequirements([
                []
            ]),
        ]);

        $json = Writer::writeToJson($openapiJson);

        $this->assertEquals(preg_replace('~\R~', "\n", <<<JSON
{
    "openapi": "3.0.0",
    "info": {
        "title": "Test API",
        "version": "1.0.0"
    },
    "paths": {},
    "security": [
        {}
    ]
}
JSON
        ),
            $json
        );
    }

    private function createOpenAPI($merge = [])
    {
        return new OpenApi(array_merge([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
            ],
            'paths' => [],
        ], $merge));
    }
}
