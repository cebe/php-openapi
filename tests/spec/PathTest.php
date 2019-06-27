<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
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

}
