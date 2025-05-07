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
        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($openapi->servers);
        } else {
            $this->assertInternalType('array', $openapi->servers);
        }

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
        $this->assertNull($openapi->security); # since it is not present in spec

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
        // examples from https://github.com/OAI/OpenAPI-Specification/tree/master/examples/v3.0
        $oaiExamples = [
            // TODO symfony/yaml can not read this file!?
//            __DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/api-with-examples.yaml',
            __DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/callback-example.yaml',
            __DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/link-example.yaml',
            __DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/petstore.yaml',
            __DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/petstore-expanded.yaml',
            __DIR__ . '/../../vendor/oai/openapi-specification/examples/v3.0/uspto.yaml',
        ];

        // examples from https://github.com/Mermade/openapi3-examples
        $mermadeExamples = [
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/externalPathItemRef.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/deprecated.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/swagger2openapi/openapi.json',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._Different_parameters.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._Fixed_file.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._Different_parameters.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._Fixed_file.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._Fixed_multipart.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._Improved_examples.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._Improved_pathdescriptions.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._Improved_securityschemes.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._Improved_serverseverywhere.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._New_callbacks.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example1_from_._New_links.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example2_from_._Different_parameters.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example2_from_._Different_requestbody.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example2_from_._Different_servers.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example2_from_._Fixed_multipart.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example2_from_._Improved_securityschemes.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example2_from_._New_callbacks.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example2_from_._New_links.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example3_from_._Different_parameters.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example3_from_._Different_servers.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example4_from_._Different_parameters.md.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/gluecon/example5_from_._Different_parameters.md.yaml',
            // TODO symfony/yaml can not read this file!?
//            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/OAI/api-with-examples.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/OAI/petstore-expanded.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/OAI/petstore.yaml',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/pass/OAI/uber.yaml',

            __DIR__ . '/../../vendor/mermade/openapi3-examples/malicious/rapid7-html.json',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/malicious/rapid7-java.json',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/malicious/rapid7-js.json',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/malicious/rapid7-php.json',
            __DIR__ . '/../../vendor/mermade/openapi3-examples/malicious/rapid7-ruby.json',
//            __DIR__ . '/../../vendor/mermade/openapi3-examples/malicious/yamlbomb.yaml',
        ];

        // examples from https://github.com/APIs-guru/openapi-directory/tree/openapi3.0.0/APIs
        $apisGuruExamples = [];
        /** @var $it RecursiveDirectoryIterator|RecursiveIteratorIterator */
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../vendor/apis-guru/openapi-directory/APIs'));
        $it->rewind();
        while($it->valid()) {
            if ($it->getBasename() === 'openapi.yaml') {
                $apisGuruExamples[] = $it->key();
            }
            $it->next();
        }

        // examples from https://github.com/Nexmo/api-specification/tree/master/definitions
        $nexmoExamples = [];
        /** @var $it RecursiveDirectoryIterator|RecursiveIteratorIterator */
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/../../vendor/nexmo/api-specification/definitions'));
        $it->rewind();
        while($it->valid()) {
            if ($it->getExtension() === 'yml'
             && strpos($it->getSubPath(), 'common') === false
             && $it->getBasename() !== 'voice.v2.yml' // contains invalid references
            ) {
                $nexmoExamples[] = $it->key();
            }
            $it->next();
        }

        $all = array_merge(
            $oaiExamples,
            $mermadeExamples,
            $apisGuruExamples,
            $nexmoExamples
        );
        foreach($all as $path) {
            yield [
                substr($path, strlen(__DIR__ . '/../../vendor/')),
                basename(dirname($path, 2)) . DIRECTORY_SEPARATOR . basename(dirname($path, 1)) . DIRECTORY_SEPARATOR . basename($path)
            ];
        }
    }

    /**
     * @dataProvider specProvider
     */
    public function testSpecs($openApiFile)
    {
        if (strtolower(substr($openApiFile, -5, 5)) === '.json') {
            $json = json_decode(file_get_contents(__DIR__ . '/../../vendor/' . $openApiFile), true);
            $openapi = new OpenApi($json);
        } else {
            $yaml = Yaml::parse(file_get_contents(__DIR__ . '/../../vendor/' . $openApiFile));
            $openapi = new OpenApi($yaml);
        }
        $openapi->setDocumentContext($openapi, new \cebe\openapi\json\JsonPointer(''));

        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors(), print_r($openapi->getErrors(), true));
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
        $openapi->security !== null && $this->assertInstanceOf(\cebe\openapi\spec\SecurityRequirements::class, $openapi->security);
        $openapi->security !== null && $this->assertAllInstanceOf(\cebe\openapi\spec\SecurityRequirement::class, $openapi->security->getRequirements());

        // tags
        $this->assertAllInstanceOf(\cebe\openapi\spec\Tag::class, $openapi->tags);

        // externalDocs
        if ($openapi->externalDocs !== null) {
            $this->assertInstanceOf(\cebe\openapi\spec\ExternalDocumentation::class, $openapi->externalDocs);
        }

    }
}
