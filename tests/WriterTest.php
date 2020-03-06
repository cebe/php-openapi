<?php

use cebe\openapi\spec\SecurityRequirement;

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
            'security' => [new SecurityRequirement(['Bearer' => []])],
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
            'security' => [new SecurityRequirement(['Bearer' => []])],
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
}
