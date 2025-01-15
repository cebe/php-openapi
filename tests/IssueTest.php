<?php

class IssueTest extends \PHPUnit\Framework\TestCase
{
    // https://github.com/cebe/php-openapi/issues/220
    public function test220WhenRunningPhpOpenapiCommandInScriptOutputShouldNotBePrinted()
    {
//        $openapi = Reader::readFromYamlFile(__DIR__.'/data/issue/220/spec.yml');
//        $openapi->performValidation();
//        $this->assertTrue($openapi->getErrors());
//        $this->assertTrue($openapi->validate());


//         exec('echo hi 123', $output, $code);
        exec('bin/php-openapi validate tests/data/issue/220/spec.yml 2>&1', $output, $code);
        $this->assertSame($output, ['/path/to/php-openapi']);
         $this->assertSame(0, $code);
    }
}
