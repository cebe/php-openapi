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

//        return; # TODO
//        $openapi = Reader::readFromJsonFile(__DIR__.'/data/issue/238/spec.json');
//        $this->assertInstanceOf(\cebe\openapi\SpecObjectInterface::class, $openapi);
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
