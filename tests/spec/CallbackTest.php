<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\Callback;

/**
 * @covers \cebe\openapi\spec\Callback
 */
class CallbackTest extends \PHPUnit\Framework\TestCase
{
    public function testRead()
    {
        /** @var $callback \cebe\openapi\spec\Callback */
        $callback = Reader::readFromYaml(<<<'YAML'
'http://notificationServer.com?transactionId={$request.body#/id}&email={$request.body#/email}':
  post:
    requestBody:
      description: Callback payload
      content: 
        'application/json':
          schema:
            $ref: '#/components/schemas/SomePayload'
    responses:
      '200':
        description: webhook successfully processed and no retries will be performed
YAML
            , Callback::class);

        $result = $callback->validate();
        $this->assertEquals([], $callback->getErrors());
        $this->assertTrue($result);

        $this->assertEquals('http://notificationServer.com?transactionId={$request.body#/id}&email={$request.body#/email}', $callback->getUrl());
        $this->assertInstanceOf(\cebe\openapi\spec\PathItem::class, $callback->getRequest());
    }

}