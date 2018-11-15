<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Contact;
use cebe\openapi\spec\Info;
use cebe\openapi\spec\License;

/**
 * @covers \cebe\openapi\spec\Info
 * @covers \cebe\openapi\spec\Contact
 * @covers \cebe\openapi\spec\License
 */
class InfoTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $info Info */
        $info = Reader::readFromYaml(<<<'YAML'
title: Sample Pet Store App
description: This is a sample server for a pet store.
termsOfService: http://example.com/terms/
contact:
  name: API Support
  url: http://www.example.com/support
  email: support@example.com
license:
  name: Apache 2.0
  url: https://www.apache.org/licenses/LICENSE-2.0.html
version: 1.0.1
YAML
            , Info::class);

        $result = $info->validate();
        $this->assertEquals([], $info->getErrors());
        $this->assertTrue($result);

        $this->assertEquals('Sample Pet Store App', $info->title);
        $this->assertEquals('This is a sample server for a pet store.', $info->description);
        $this->assertEquals('http://example.com/terms/', $info->termsOfService);
        $this->assertEquals('1.0.1', $info->version);

        $this->assertInstanceOf(Contact::class, $info->contact);
        $this->assertEquals('API Support', $info->contact->name);
        $this->assertEquals('http://www.example.com/support', $info->contact->url);
        $this->assertEquals('support@example.com', $info->contact->email);
        $this->assertInstanceOf(License::class, $info->license);
        $this->assertEquals('Apache 2.0', $info->license->name);
        $this->assertEquals('https://www.apache.org/licenses/LICENSE-2.0.html', $info->license->url);
    }

    public function testReadInvalid()
    {
        /** @var $info Info */
        $info = Reader::readFromYaml(<<<'YAML'
description: This is a sample server for a pet store.
termsOfService: http://example.com/terms/
contact:
  name: API Support
  url: http://www.example.com/support
  email: support@example.com
YAML
            , Info::class);

        $result = $info->validate();
        $this->assertEquals([
            'Info is missing required property: title',
            'Info is missing required property: version',
        ], $info->getErrors());
        $this->assertFalse($result);

    }

    public function testReadInvalidContact()
    {
        /** @var $info Info */
        $info = Reader::readFromYaml(<<<'YAML'
title: test
version: 1.0
contact:
  name: API Support
  url: www.example.com/support
  email: support.example.com
YAML
            , Info::class);

        $result = $info->validate();
        $this->assertEquals([
            'Contact::$email does not seem to be a valid email address: support.example.com',
            'Contact::$url does not seem to be a valid URL: www.example.com/support',
        ], $info->getErrors());
        $this->assertFalse($result);

        $this->assertInstanceOf(Contact::class, $info->contact);
        $this->assertNull($info->license);

    }

    public function testReadInvalidLicense()
    {
        /** @var $info Info */
        $info = Reader::readFromYaml(<<<'YAML'
title: test
version: 1.0
license:
  url: www.apache.org/licenses/LICENSE-2.0.html
YAML
            , Info::class);

        $result = $info->validate();
        $this->assertEquals([
            'License is missing required property: name',
            'License::$url does not seem to be a valid URL: www.apache.org/licenses/LICENSE-2.0.html',
        ], $info->getErrors());
        $this->assertFalse($result);

        $this->assertInstanceOf(License::class, $info->license);
        $this->assertNull($info->contact);

    }
}