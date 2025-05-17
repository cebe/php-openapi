<?php

use cebe\openapi\Reader;
use cebe\openapi\SpecObjectInterface;
use PHPUnit\Framework\TestCase;

// https://github.com/cebe/php-openapi/issues/242
class Issue242Test extends TestCase
{
    public function test242CliCallToOpenapiSpecWithSecurityInPathFails()
    {
        $file = dirname(__DIR__, 2) . '/data/issue/242/spec.json';
        $openapi = Reader::readFromJsonFile($file);
        $this->assertInstanceOf(SpecObjectInterface::class, $openapi);

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

    public function test242Case2()
    {
        // read in yml
        $openapi = Reader::readFromYamlFile(dirname(__DIR__, 2) . '/data/issue/242/spec2.yml');
        $this->assertInstanceOf(SpecObjectInterface::class, $openapi);
        $this->assertSame(json_decode(json_encode($openapi->paths['/endpoint']->get->security->getSerializableData()), true), [
            [
                'apiKey' => [],
                'bearerAuth' => []
            ]
        ]);

        // write in yml # TODO
        // read in json # TODO
        // write in json # TODO
    }
}
