<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Server;
use cebe\openapi\spec\ServerVariable;

/**
 * @covers \cebe\openapi\spec\Server
 * @covers \cebe\openapi\spec\ServerVariable
 */
class ServerTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $server Server */
        $server = Reader::readFromJson(<<<JSON
{
  "url": "https://{username}.gigantic-server.com:{port}/{basePath}",
  "description": "The production API server",
  "variables": {
    "username": {
      "default": "demo",
      "description": "this value is assigned by the service provider, in this example `gigantic-server.com`"
    },
    "port": {
      "enum": [
        "8443",
        "443"
      ],
      "default": "8443"
    },
    "basePath": {
      "default": "v2"
    }
  }
}
JSON
        , Server::class);

        $result = $server->validate();
        $this->assertEquals([], $server->getErrors());
        $this->assertTrue($result);

        $this->assertEquals('https://{username}.gigantic-server.com:{port}/{basePath}', $server->url);
        $this->assertEquals('The production API server', $server->description);
        $this->assertCount(3, $server->variables);
        $this->assertEquals('demo', $server->variables['username']->default);
        $this->assertEquals('this value is assigned by the service provider, in this example `gigantic-server.com`', $server->variables['username']->description);
        $this->assertEquals('8443', $server->variables['port']->default);

        /** @var $server Server */
        $server = Reader::readFromJson(<<<JSON
{
  "description": "The production API server"
}
JSON
            , Server::class);

        $result = $server->validate();
        $this->assertEquals(['Server is missing required property: url'], $server->getErrors());
        $this->assertFalse($result);


        /** @var $server Server */
        $server = Reader::readFromJson(<<<JSON
{
  "url": "https://{username}.gigantic-server.com:{port}/{basePath}",
  "description": "The production API server",
  "variables": {
    "username": {
      "description": "this value is assigned by the service provider, in this example `gigantic-server.com`"
    }
  }
}
JSON
        , Server::class);

        $result = $server->validate();
        $this->assertEquals(['ServerVariable is missing required property: default'], $server->getErrors());
        $this->assertFalse($result);
    }

}
