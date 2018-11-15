<?php

use cebe\openapi\spec\OpenApi;
use Symfony\Component\Yaml\Yaml;

class OpenApiTest extends \PHPUnit\Framework\TestCase
{
    public function testEmpty()
    {
        $openapi = new OpenApi([]);

        $this->assertFalse($openapi->validate());
        $this->assertEquals([
            'OpenApi is missing required property: openapi',
            'OpenApi is missing required property: info',
            'OpenApi is missing required property: paths',
        ], $openapi->getErrors());
    }

    public function testReadPetStore()
    {
        $openApiFile = __DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/petstore.yaml';

        $yaml = Yaml::parse(file_get_contents($openApiFile));
        $openapi = new OpenApi($yaml);

        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors());
        $this->assertTrue($result);

        // openapi
        $this->assertEquals('3.0.0', $openapi->openapi);

        // info
        $this->assertInstanceOf(\cebe\openapi\spec\Info::class, $openapi->info);
        $this->assertEquals('1.0.0', $openapi->info->version);
        $this->assertEquals('Swagger Petstore', $openapi->info->title);
        // info.license
        $this->assertInstanceOf(\cebe\openapi\spec\License::class, $openapi->info->license);
        $this->assertEquals('MIT', $openapi->info->license->name);
        // info.contact
        $this->assertNull($openapi->info->contact);


        // servers
        $this->assertInternalType('array', $openapi->servers);
        $this->assertCount(1, $openapi->servers);
        foreach ($openapi->servers as $server) {
            $this->assertInstanceOf(\cebe\openapi\spec\Server::class, $server);
            $this->assertEquals('http://petstore.swagger.io/v1', $server->url);

        }
    }
}
