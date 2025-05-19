<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\SecurityRequirement;
use cebe\openapi\spec\SecurityRequirements;
use cebe\openapi\spec\SecurityScheme;
use cebe\openapi\SpecObjectInterface;
use cebe\openapi\Writer;
use PHPUnit\Framework\TestCase;

// https://github.com/cebe/php-openapi/issues/242
class Issue242Test extends TestCase
{
    public function test242CliCallToOpenapiSpecWithSecurityInPathFails()
    {
        $file = dirname(__DIR__, 2) . '/data/issue/242/spec.json';
        $openapi = Reader::readFromJsonFile($file);
        $this->assertInstanceOf(SpecObjectInterface::class, $openapi);

        $dirSep = DIRECTORY_SEPARATOR;
        $cmd = 'php ' . dirname(__DIR__, 3) . "{$dirSep}bin{$dirSep}php-openapi validate " . $file . " 2>&1";
        exec($cmd, $op, $ec);
        $this->assertSame($this->removeCliFormatting($op[0]), 'The supplied API Description validates against the OpenAPI v3.0 schema.');
        $this->assertSame(0, $ec);
    }

    private function removeCliFormatting($string)
    {
        // Regex to remove ANSI escape codes
        return preg_replace('/\e\[[0-9;]*m/', '', $string);
    }

    public function test242Case2() # https://github.com/cebe/php-openapi/issues/242#issuecomment-2886431173
    {
        // read in yml
        $file = dirname(__DIR__, 2) . '/data/issue/242/spec2.yml';
        $openapi = Reader::readFromYamlFile($file);
        $this->assertInstanceOf(SpecObjectInterface::class, $openapi);
        $this->assertSame(json_decode(json_encode($openapi->paths['/endpoint']->get->security->getSerializableData()), true), [
            [
                'apiKey' => [],
                'bearerAuth' => []
            ]
        ]);

        # write back to yml
        $yaml = Writer::writeToYaml($openapi);
        $this->assertEquals(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'API Documentation'
  description: 'All API endpoints are presented here.'
  version: 1.0.0
servers:
  -
    url: 'http://127.0.0.1:8080/'
paths:
  /endpoint:
    get:
      responses:
        '200':
          description: OK
      security:
        -
          apiKey: []
          bearerAuth: []
components:
  securitySchemes:
    apiKey:
      type: apiKey
      name: X-APi-Key
      in: header
    bearerAuth:
      type: http
      description: 'JWT Authorization header using the Bearer scheme.'
      scheme: bearer
      bearerFormat: JWT

YAML
        ),
            $yaml
        );

        // read in json
        $file = dirname(__DIR__, 2) . '/data/issue/242/spec2.json';
        $openapi = Reader::readFromJsonFile($file);
        $this->assertInstanceOf(SpecObjectInterface::class, $openapi);
        $this->assertSame(json_decode(json_encode($openapi->paths['/endpoint']->get->security->getSerializableData()), true), [
            [
                'apiKey' => [],
                'bearerAuth' => []
            ]
        ]);

        // write back in json
        $yaml = Writer::writeToJson($openapi);
        $this->assertEquals(preg_replace('~\R~', "\n", <<<JSON
{
    "openapi": "3.0.0",
    "info": {
        "title": "API Documentation",
        "description": "All API endpoints are presented here.",
        "version": "1.0.0"
    },
    "servers": [
        {
            "url": "http:\/\/127.0.0.1:8080\/"
        }
    ],
    "paths": {
        "\/endpoint": {
            "get": {
                "responses": {
                    "200": {
                        "description": "OK"
                    }
                },
                "security": [
                    {
                        "apiKey": [],
                        "bearerAuth": []
                    }
                ]
            }
        }
    },
    "components": {
        "securitySchemes": {
            "apiKey": {
                "type": "apiKey",
                "name": "X-APi-Key",
                "in": "header"
            },
            "bearerAuth": {
                "type": "http",
                "description": "JWT Authorization header using the Bearer scheme.",
                "scheme": "bearer",
                "bearerFormat": "JWT"
            }
        }
    }
}
JSON
        ),
            $yaml
        );
    }

    public function test242Case3MultipleAuth()
    {
        $file = dirname(__DIR__, 2) . '/data/issue/242/multiple_auth.yml';
        $openapi = Reader::readFromYamlFile($file);
        $this->assertInstanceOf(SpecObjectInterface::class, $openapi);
        $act = json_decode(json_encode($openapi->security->getSerializableData()), true);
        $this->assertSame([], $act[0]['BasicAuth']);

        # write back to yml
        $yaml = Writer::writeToYaml($openapi);
        $this->assertEquals(preg_replace('~\R~', "\n", <<<YAML
openapi: 3.0.0
info:
  title: 'Multiple auth'
  version: 1.0.0
paths: {  }
components:
  securitySchemes:
    BasicAuth:
      type: http
      scheme: basic
    BearerAuth:
      type: http
      scheme: bearer
    ApiKeyAuth:
      type: apiKey
      name: X-API-Key
      in: header
    OpenID:
      type: openIdConnect
      openIdConnectUrl: 'https://example.com/.well-known/openid-configuration'
    OAuth2:
      type: oauth2
      flows:
        authorizationCode:
          authorizationUrl: 'https://example.com/oauth/authorize'
          tokenUrl: 'https://example.com/oauth/token'
          scopes:
            read: 'Grants read access'
            write: 'Grants write access'
            admin: 'Grants access to admin operations'
security:
  -
    BasicAuth: []
    BearerAuth: []
  -
    ApiKeyAuth: []
    OAuth2:
      - read

YAML
        ),
            $yaml
        );
    }

    public function test242Case4WriteMultipleAuth()
    {
        $openapi = $this->createOpenAPI([
            'components' => new Components([
                'securitySchemes' => [
                    'BearerAuth' => new SecurityScheme([
                        'type' => 'http',
                        'scheme' => 'bearer',
                    ]),
                    'BasicAuth' => new SecurityScheme([
                        'type' => 'http',
                        'scheme' => 'basic',
                    ]),
                    'ApiKeyAuth' => new SecurityScheme([
                        'type' => 'apiKey',
                        'name' => 'X-API-Key',
                        'in' => 'header'
                    ])
                ],
            ]),
            'security' => new SecurityRequirements([
                [
                    'BearerAuth' => new SecurityRequirement([]),
                    'BasicAuth' => new SecurityRequirement([])
                ],
                [
                    'ApiKeyAuth' => new SecurityRequirement([])
                ]
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
    BasicAuth:
      type: http
      scheme: basic
    ApiKeyAuth:
      type: apiKey
      name: X-API-Key
      in: header
security:
  -
    BearerAuth: []
    BasicAuth: []
  -
    ApiKeyAuth: []

YAML
        ),
            $yaml
        );
    }

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
}
