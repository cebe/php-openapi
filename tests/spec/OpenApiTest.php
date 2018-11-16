<?php

use cebe\openapi\spec\OpenApi;
use Symfony\Component\Yaml\Yaml;

/**
 * @covers \cebe\openapi\spec\OpenApi
 */
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

        // check default value of servers
        // https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#openapiObject
        // If the servers property is not provided, or is an empty array, the default value would be a Server Object with a url value of /.
        $this->assertCount(1, $openapi->servers);
        $this->assertEquals('/', $openapi->servers[0]->url);
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

        // paths
        $this->assertInstanceOf(\cebe\openapi\spec\Paths::class, $openapi->paths);

        // components
        $this->assertInstanceOf(\cebe\openapi\spec\Components::class, $openapi->components);

        // security
        $this->assertAllInstanceOf(\cebe\openapi\spec\SecurityRequirement::class, $openapi->security);

        // tags
        $this->assertAllInstanceOf(\cebe\openapi\spec\Tag::class, $openapi->tags);

        // externalDocs
        $this->assertNull($openapi->externalDocs);
    }

    public function assertAllInstanceOf($className, $array)
    {
        foreach($array as $k => $v) {
            $this->assertInstanceOf($className, $v, "Asserting that item with key '$k' is instance of $className");
        }
    }

    public function specProvider()
    {
        return [
            // TODO symfony/yaml can not read this file!?
//            [__DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/api-with-examples.yaml'],
            [__DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/callback-example.yaml'],
            [__DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/link-example.yaml'],
            [__DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/petstore.yaml'],
            [__DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/petstore-expanded.yaml'],
            [__DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/uspto.yaml'],
        ];
    }

    /**
     * @dataProvider specProvider
     */
    public function testSpecs($openApiFile)
    {
        $yaml = Yaml::parse(file_get_contents($openApiFile));
        $openapi = new OpenApi($yaml);

        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors());
        $this->assertTrue($result);

        // openapi
        $this->assertStringStartsWith('3.0.', $openapi->openapi);

        // info
        $this->assertInstanceOf(\cebe\openapi\spec\Info::class, $openapi->info);

        // servers
        $this->assertAllInstanceOf(\cebe\openapi\spec\Server::class, $openapi->servers);

        // paths
        if ($openapi->components !== null) {
            $this->assertInstanceOf(\cebe\openapi\spec\Paths::class, $openapi->paths);
        }

        // components
        if ($openapi->components !== null) {
            $this->assertInstanceOf(\cebe\openapi\spec\Components::class, $openapi->components);
        }

        // security
        $this->assertAllInstanceOf(\cebe\openapi\spec\SecurityRequirement::class, $openapi->security);

        // tags
        $this->assertAllInstanceOf(\cebe\openapi\spec\Tag::class, $openapi->tags);

        // externalDocs
        $this->assertNull($openapi->externalDocs);

    }
}
