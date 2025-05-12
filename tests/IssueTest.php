<?php

use cebe\openapi\Reader;

class IssueTest extends \PHPUnit\Framework\TestCase
{
    // https://github.com/cebe/php-openapi/issues/175
    public function test175UnableToReferenceOtherLocalJsonFile()
    {
        $openapi = Reader::readFromJsonFile(__DIR__.'/data/issue/175/spec.json');
        $this->assertInstanceOf(\cebe\openapi\SpecObjectInterface::class, $openapi);
    }

    // https://github.com/cebe/php-openapi/issues/224
    public function test224FailsOnLargeDefinitions()
    {
        $openapi = Reader::readFromJsonFile(__DIR__.'/data/issue/224/cloudflare.json');
        $this->assertInstanceOf(\cebe\openapi\SpecObjectInterface::class, $openapi);
        $this->assertSame($openapi->openapi, '3.0.3');
        $this->assertSame(
            $openapi->paths->getPath('/memberships')->getOperations()['get']->responses['4XX']->content['application/json']->schema->properties['success']->description,
            'Whether the API call was successful.'
        );
    }
}
