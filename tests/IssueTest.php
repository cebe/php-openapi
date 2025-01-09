<?php

use cebe\openapi\Reader;

class IssueTest extends \PHPUnit\Framework\TestCase
{
    // https://github.com/cebe/php-openapi/issues/165
    public function test165WrongErrorMessageWhenUsingAnUndefinedPropertyForASchemaObjectPropertyDefinition()
    {
        $openapi = Reader::readFromYamlFile(__DIR__.'/data/issue/165/spec.yml');
        $this->assertTrue($openapi->validate());
    }
}
