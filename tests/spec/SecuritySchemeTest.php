<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\OAuthFlow;
use cebe\openapi\spec\OAuthFlows;
use cebe\openapi\spec\SecurityRequirement;
use cebe\openapi\spec\SecurityScheme;

/**
 * @covers \cebe\openapi\spec\SecurityScheme
 * @covers \cebe\openapi\spec\OAuthFlows
 * @covers \cebe\openapi\spec\OAuthFlow
 * @covers \cebe\openapi\spec\SecurityRequirement
 */
class SecuritySchemeTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $securityScheme SecurityScheme */
        $securityScheme = Reader::readFromYaml(<<<YAML
type: http
scheme: basic
YAML
        , SecurityScheme::class);

        $result = $securityScheme->validate();
        $this->assertEquals([], $securityScheme->getErrors());
        $this->assertTrue($result);

        $this->assertEquals('http', $securityScheme->type);
        $this->assertEquals('basic', $securityScheme->scheme);

        /** @var $securityScheme SecurityScheme */
        $securityScheme = Reader::readFromYaml(<<<YAML
scheme: basic
YAML
        , SecurityScheme::class);

        $result = $securityScheme->validate();
        $this->assertEquals(['SecurityScheme is missing required property: type'], $securityScheme->getErrors());
        $this->assertFalse($result);

        /** @var $securityScheme SecurityScheme */
        $securityScheme = Reader::readFromYaml(<<<YAML
type: apiKey
YAML
            , SecurityScheme::class);

        $result = $securityScheme->validate();
        $this->assertEquals([
            'SecurityScheme is missing required property: name',
            'SecurityScheme is missing required property: in',
        ], $securityScheme->getErrors());
        $this->assertFalse($result);

        /** @var $securityScheme SecurityScheme */
        $securityScheme = Reader::readFromYaml(<<<YAML
type: http
YAML
            , SecurityScheme::class);

        $result = $securityScheme->validate();
        $this->assertEquals([
            'SecurityScheme is missing required property: scheme',
        ], $securityScheme->getErrors());
        $this->assertFalse($result);

        /** @var $securityScheme SecurityScheme */
        $securityScheme = Reader::readFromYaml(<<<YAML
type: oauth2
YAML
            , SecurityScheme::class);

        $result = $securityScheme->validate();
        $this->assertEquals([
            'SecurityScheme is missing required property: flows',
        ], $securityScheme->getErrors());
        $this->assertFalse($result);

        /** @var $securityScheme SecurityScheme */
        $securityScheme = Reader::readFromYaml(<<<YAML
type: openIdConnect
YAML
            , SecurityScheme::class);

        $result = $securityScheme->validate();
        $this->assertEquals([
            'SecurityScheme is missing required property: openIdConnectUrl',
        ], $securityScheme->getErrors());
        $this->assertFalse($result);
    }

    public function testOAuth2()
    {
        /** @var $securityScheme SecurityScheme */
        $securityScheme = Reader::readFromYaml(<<<YAML
type: oauth2
flows:
  implicit:
    authorizationUrl: https://example.com/api/oauth/dialog
YAML
            , SecurityScheme::class);

        $result = $securityScheme->validate();
        $this->assertEquals([
            'OAuthFlow is missing required property: scopes',
        ], $securityScheme->getErrors());
        $this->assertFalse($result);

        /** @var $securityScheme SecurityScheme */
        $securityScheme = Reader::readFromYaml(<<<YAML
type: oauth2
flows:
  implicit:
    authorizationUrl: https://example.com/api/oauth/dialog
    scopes:
      write:pets: modify pets in your account
      read:pets: read your pets
  authorizationCode:
    authorizationUrl: https://example.com/api/oauth/dialog
    tokenUrl: https://example.com/api/oauth/token
    scopes:
      write:pets: modify pets in your account
      read:pets: read your pets 
YAML
            , SecurityScheme::class);

        $result = $securityScheme->validate();
        $this->assertEquals([], $securityScheme->getErrors());
        $this->assertTrue($result);

        $this->assertInstanceOf(OAuthFlows::class, $securityScheme->flows);
        $this->assertInstanceOf(OAuthFlow::class, $securityScheme->flows->implicit);
        $this->assertInstanceOf(OAuthFlow::class, $securityScheme->flows->authorizationCode);
        $this->assertNull($securityScheme->flows->clientCredentials);
        $this->assertNull($securityScheme->flows->password);

        $this->assertEquals('https://example.com/api/oauth/dialog', $securityScheme->flows->implicit->authorizationUrl);
        $this->assertEquals([
            'write:pets' => 'modify pets in your account',
            'read:pets' =>  'read your pets',
        ], $securityScheme->flows->implicit->scopes);
    }

    public function testSecurityRequirement()
    {
        /** @var $securityRequirement SecurityRequirement */
        $securityRequirement = Reader::readFromYaml(<<<YAML
api_key: []
YAML
            , SecurityRequirement::class);

        $result = $securityRequirement->validate();
        $this->assertEquals([], $securityRequirement->getErrors());
        $this->assertTrue($result);

        $this->assertSame([], $securityRequirement->api_key);

        /** @var $securityRequirement SecurityRequirement */
        $securityRequirement = Reader::readFromYaml(<<<YAML
petstore_auth:
- write:pets
- read:pets
YAML
            , SecurityRequirement::class);

        $result = $securityRequirement->validate();
        $this->assertEquals([], $securityRequirement->getErrors());
        $this->assertTrue($result);

        $this->assertSame(['write:pets', 'read:pets'], $securityRequirement->petstore_auth);
    }
}
