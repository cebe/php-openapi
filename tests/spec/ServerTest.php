<?php

use Symfony\Component\Yaml\Yaml;

/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class ServerTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $server \cebe\openapi\spec\Server */
        $server = \cebe\openapi\Reader::readFromJson(<<<JSON
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
        , \cebe\openapi\spec\Server::class);

        $result = $server->validate();
        $this->assertEquals([], $server->getErrors());
        $this->assertTrue($result);

        $this->assertEquals("https://{username}.gigantic-server.com:{port}/{basePath}", $server->url);
        $this->assertEquals("The production API server", $server->description);
        $this->assertCount(3, $server->variables);
        $this->assertEquals("demo", $server->variables['username']->default);
        $this->assertEquals("this value is assigned by the service provider, in this example `gigantic-server.com`", $server->variables['username']->description);
        $this->assertEquals("8443", $server->variables['port']->default);
    }

}
