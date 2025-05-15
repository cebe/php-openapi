<?php

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\SecurityRequirements;
use cebe\openapi\Writer;

// https://github.com/cebe/php-openapi/issues/242
class Issue242Test extends \PHPUnit\Framework\TestCase
{
    public function test242CliCallToOpenapiSpecWithSecurityInPathFails()
    {
        $openapi = Reader::readFromJsonFile(dirname(__DIR__, 2) . '/data/issue/242/spec.json');
        $this->assertInstanceOf(\cebe\openapi\SpecObjectInterface::class, $openapi);

        $file = dirname(__DIR__, 2) . '/data/issue/242/spec.json';
        $dirSep = DIRECTORY_SEPARATOR;
        $cmd = 'php ' . dirname(__DIR__, 3) . "{$dirSep}bin{$dirSep}php-openapi validate " . $file . " 2>&1";
        exec($cmd, $op, $ec);
        $this->assertSame($this->removeCliFormatting($op[0]), 'The supplied API Description validates against the OpenAPI v3.0 schema.');
        $this->assertSame(0, $ec);
    }

    private function removeCliFormatting($string)
    {
        // Regex to remove ANSI escape codes
        return preg_replace('/\e\[[0-9;]*m/', '', $string);
    }
}