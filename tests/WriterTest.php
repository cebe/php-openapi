<?php

class WriterTest extends \PHPUnit\Framework\TestCase
{
    private function createOpenAPI()
    {
        return new \cebe\openapi\spec\OpenApi([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Test API',
                'version' => '1.0.0',
            ],
            'paths' => [],
        ]);
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
}
