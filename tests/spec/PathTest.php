<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Responses;

/**
 * @covers \cebe\openapi\spec\Paths
 * @covers \cebe\openapi\spec\PathItem
 */
class PathTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $paths Paths */
        $paths = Reader::readFromJson(<<<'JSON'
{
  "/pets": {
    "get": {
      "description": "Returns all pets from the system that the user has access to",
      "responses": {
        "200": {
          "description": "A list of pets.",
          "content": {
            "application/json": {
              "schema": {
                "type": "array",
                "items": {
                  "$ref": "#/components/schemas/pet"
                }
              }
            }
          }
        }
      }
    }
  }
}
JSON
        , Paths::class);

        $result = $paths->validate();
        $this->assertEquals([], $paths->getErrors());
        $this->assertTrue($result);

        $this->assertTrue($paths->hasPath('/pets'));
        $this->assertTrue(isset($paths['/pets']));
        $this->assertFalse($paths->hasPath('/dog'));
        $this->assertFalse(isset($paths['/dog']));

        $this->assertInstanceOf(PathItem::class, $paths->getPath('/pets'));
        $this->assertInstanceOf(PathItem::class, $paths['/pets']);
        $this->assertInstanceOf(Operation::class, $paths->getPath('/pets')->get);
        $this->assertNull($paths->getPath('/dog'));
        $this->assertNull($paths['/dog']);

        $this->assertCount(1, $paths->getPaths());
        $this->assertCount(1, $paths);
        foreach($paths as $path => $pathItem) {
            $this->assertEquals('/pets', $path);
            $this->assertInstanceOf(PathItem::class, $pathItem);
        }
    }

    public function testCreateionFromObjects()
    {
        $paths = new Paths([
            '/pets' => new PathItem([
                'get' => new Operation([
                    'responses' => new Responses([
                        200 => new Response(['description' => 'A list of pets.']),
                        404 => ['description' => 'The pets list is gone 🙀'],
                    ])
                ])
            ])
        ]);

        $this->assertTrue($paths->hasPath('/pets'));
        $this->assertInstanceOf(PathItem::class, $paths->getPath('/pets'));
        $this->assertInstanceOf(PathItem::class, $paths['/pets']);
        $this->assertInstanceOf(Operation::class, $paths->getPath('/pets')->get);

        $this->assertSame('A list of pets.', $paths->getPath('/pets')->get->responses->getResponse(200)->description);
        $this->assertSame('The pets list is gone 🙀', $paths->getPath('/pets')->get->responses->getResponse(404)->description);
    }

    public function badPathsConfigProvider()
    {
        yield [['/pets' => 'foo'], 'Path MUST be either array or PathItem object, "string" given'];
        yield [['/pets' => 42], 'Path MUST be either array or PathItem object, "integer" given'];
        yield [['/pets' => false], 'Path MUST be either array or PathItem object, "boolean" given'];
        yield [['/pets' => new stdClass()], 'Path MUST be either array or PathItem object, "stdClass" given'];
        // The last one can be supported in future, but now SpecBaseObjects::__construct() requires array explicitly
    }

    /**
     * @dataProvider badPathsConfigProvider
     */
    public function testPathsCanNotBeCreatedFromBullshit($config, $expectedException)
    {
        $this->expectException(\cebe\openapi\exceptions\TypeErrorException::class);
        $this->expectExceptionMessage($expectedException);

        new Paths($config);
    }

    public function testInvalidPath()
    {
        /** @var $paths Paths */
        $paths = Reader::readFromJson(<<<'JSON'
{
  "pets": {
    "get": {
      "description": "Returns all pets from the system that the user has access to",
      "responses": {
        "200": {
          "description": "A list of pets."
        }
      }
    }
  }
}
JSON
            , Paths::class);

        $result = $paths->validate();
        $this->assertEquals([
            'Path must begin with /: pets'
        ], $paths->getErrors());
        $this->assertFalse($result);
    }

    public function testPathItemReference()
    {
        $file = __DIR__ . '/data/paths/openapi.yaml';
        /** @var $openapi \cebe\openapi\spec\OpenApi */
        $openapi = Reader::readFromYamlFile($file, \cebe\openapi\spec\OpenApi::class, false);

        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors(), print_r($openapi->getErrors(), true));
        $this->assertTrue($result);

        $this->assertInstanceOf(Paths::class, $openapi->paths);
        $this->assertInstanceOf(PathItem::class, $fooPath = $openapi->paths['/foo']);
        $this->assertInstanceOf(PathItem::class, $barPath = $openapi->paths['/bar']);
        $this->assertSame([
            'x-extension-1' => 'Extension1',
            'x-extension-2' => 'Extension2'
        ], $openapi->getExtensions());

        $this->assertEmpty($fooPath->getOperations());
        $this->assertEmpty($barPath->getOperations());

        $this->assertInstanceOf(\cebe\openapi\spec\Reference::class, $fooPath->getReference());
        $this->assertInstanceOf(\cebe\openapi\spec\Reference::class, $barPath->getReference());

        $this->assertNull($fooPath->getReference()->resolve());
        $this->assertInstanceOf(PathItem::class, $ReferencedBarPath = $barPath->getReference()->resolve());

        $this->assertCount(1, $ReferencedBarPath->getOperations());
        $this->assertInstanceOf(Operation::class, $ReferencedBarPath->get);
        $this->assertEquals('getBar', $ReferencedBarPath->get->operationId);

        $this->assertInstanceOf(Reference::class, $reference200 = $ReferencedBarPath->get->responses['200']);
        $this->assertInstanceOf(Response::class, $ReferencedBarPath->get->responses['404']);
        $this->assertEquals('non-existing resource', $ReferencedBarPath->get->responses['404']->description);

        $path200 = $reference200->resolve();
        $this->assertInstanceOf(Response::class, $path200);
        $this->assertEquals('A bar', $path200->description);

        /** @var $openapi OpenApi */
        $openapi = Reader::readFromYamlFile($file, \cebe\openapi\spec\OpenApi::class, true);

        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors(), print_r($openapi->getErrors(), true));
        $this->assertTrue($result);

        $this->assertInstanceOf(Paths::class, $openapi->paths);
        $this->assertInstanceOf(PathItem::class, $fooPath = $openapi->paths['/foo']);
        $this->assertInstanceOf(PathItem::class, $barPath = $openapi->paths['/bar']);

        $this->assertEmpty($fooPath->getOperations());
        $this->assertCount(1, $barPath->getOperations());
        $this->assertInstanceOf(Operation::class, $barPath->get);
        $this->assertEquals('getBar', $barPath->get->operationId);

        $this->assertEquals('A bar', $barPath->get->responses['200']->description);
        $this->assertEquals('non-existing resource', $barPath->get->responses['404']->description);
    }

    public function testPathParametersAreArrays()
    {
        $file = __DIR__ . '/data/path-params/openapi.yaml';
        /** @var $openapi \cebe\openapi\spec\OpenApi */
        $openapi = Reader::readFromYamlFile($file, \cebe\openapi\spec\OpenApi::class, true);

        $result = $openapi->validate();
        $this->assertEquals([], $openapi->getErrors(), print_r($openapi->getErrors(), true));
        $this->assertTrue($result);

        $this->assertInstanceOf(Paths::class, $openapi->paths);
        $this->assertSame(gettype($openapi->paths->getPaths()), 'array');
        $this->assertInstanceOf(PathItem::class, $usersPath = $openapi->paths['/v1/organizations/{organizationId}/user']);
        $this->assertInstanceOf(PathItem::class, $userIdPath = $openapi->paths['/v1/organizations/{organizationId}/user/{id}']);

        $result = $usersPath->validate();
        $this->assertTrue($result);
        $this->assertSame(gettype($usersPath->parameters), 'array');
        $this->assertInstanceOf(\cebe\openapi\spec\Parameter::class, $usersPath->parameters[0]);
        $this->assertInstanceOf(\cebe\openapi\spec\Parameter::class, $usersPath->parameters[1]);
        $this->assertEquals('api-version', $usersPath->parameters[0]->name);

        $result = $userIdPath->validate();
        $this->assertTrue($result);
        $this->assertSame(gettype($userIdPath->parameters), 'array');
        $this->assertInstanceOf(\cebe\openapi\spec\Parameter::class, $userIdPath->parameters[0]);
        $this->assertInstanceOf(\cebe\openapi\spec\Parameter::class, $userIdPath->parameters[1]);
        $this->assertEquals('id', $userIdPath->parameters[2]->name);

        $dirSep = DIRECTORY_SEPARATOR;
        $output = dirname(__DIR__) . $dirSep . 'compiled.yml';
        shell_exec('php ' . dirname(__DIR__, 2) . "{$dirSep}bin{$dirSep}php-openapi inline " . $file . ' ' . $output);

        $baseExpected = dirname(__DIR__)."{$dirSep}data{$dirSep}issue{$dirSep}155{$dirSep}";
        $expected = $baseExpected.'compiled-symfony-7.yml';
        $version = static::symfonyYamlVersion();
        $majorVersion = explode('.', $version)[0];

        if ($majorVersion == 6) {
            $expected = $baseExpected."compiled-symfony-6.yml";
            if (version_compare(PHP_VERSION, '8.1', '>=') && version_compare($version, '6.0.0', '!=')) {
                $expected = $baseExpected."compiled-symfony-7.yml";
            }
        } elseif ($majorVersion == 5) {
            $expected = $baseExpected."compiled-symfony-6.yml";
        }
        if (stripos(PHP_OS, 'WIN') === 0) { # fixes https://github.com/cebe/php-openapi/actions/runs/14808968938/job/41581244210
            file_put_contents($expected, preg_replace('~\R~', "\n", file_get_contents($expected))); # not an ideal solution, can be refactored  
        }

        $this->assertFileEquals($output, $expected);
        unlink($output);
    }

    protected static function symfonyYamlVersion()
    {
        $package = 'symfony/yaml';
        $installed = json_decode(file_get_contents(__DIR__ . '/../../composer.lock'), true);

        foreach ($installed['packages'] as $pkg) {
            if ($pkg['name'] === $package) {
                return str_replace('v', '', $pkg['version']);
            }
        }
        return '7.0.0';
    }
}
