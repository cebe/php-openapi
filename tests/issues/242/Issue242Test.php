<?php

use cebe\openapi\Reader;
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
        $json = Writer::writeToYaml($openapi);
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
            $json
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
        $json = Writer::writeToJson($openapi);
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
            $json
        );
    }
}
