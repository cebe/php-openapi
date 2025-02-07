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
}
