<?php

class IssueTest extends \PHPUnit\Framework\TestCase
{
    // https://github.com/cebe/php-openapi/issues/165
    public function test165WrongErrorMessageWhenUsingAnUndefinedPropertyForASchemaObjectPropertyDefinition()
    {
//        $openapi = Reader::readFromYamlFile(__DIR__.'/data/issue/165/spec.yml');
//        $openapi->performValidation();
//        $this->assertTrue($openapi->getErrors());
//        $this->assertTrue($openapi->validate());


        // exec('echo hiiiiiiiii', $output, $code);
        exec('bin/php-openapi validate tests/data/issue/165/spec.yml', $output, $code);
        $this->assertSame($output, ['/path/to/php-openapi']);
        // $this->assertSame(0, $code);
    }
}
