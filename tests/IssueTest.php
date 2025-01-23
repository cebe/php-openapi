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

        // exec('echo hi', $output, $code);
        exec('bin/php-openapi validate tests/data/issue/165/spec.yml 2>&1', $output, $code);
        $this->assertSame($output, [
            'OpenAPI v3.0 schema violations:',
            '- [components.schemas.answer.properties.id] The property summary is not defined and the definition does not allow additional properties',
        ]);
        // $this->assertSame(0, $code);
    }
}
