<?php

use cebe\openapi\Reader;

// https://github.com/cebe/php-openapi/issues/238
class Issue238Test extends \PHPUnit\Framework\TestCase
{
    public function test238AddSupportForEmptySecurityRequirementObjectInSecurityRequirement()
    {
        $openapi = Reader::readFromYamlFile(dirname(dirname(__DIR__)).'/data/issue/238/spec.yml');
        $this->assertInstanceOf(\cebe\openapi\SpecObjectInterface::class, $openapi);
        $this->assertInstanceOf(\cebe\openapi\spec\SecurityRequirements::class, $openapi->paths->getPath('/path-secured')->getOperations()['get']->security);
        $this->assertSame($openapi->paths->getPath('/path-secured')->getOperations()['get']->security->getSerializableData(), [[]]);

//        return; # TODO
//        $openapi = Reader::readFromJsonFile(__DIR__.'/data/issue/238/spec.json');
//        $this->assertInstanceOf(\cebe\openapi\SpecObjectInterface::class, $openapi);
    }
}
