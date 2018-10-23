<?php

namespace cebe\openapi;

use cebe\openapi\spec\OpenApi;
use Symfony\Component\Yaml\Yaml;

/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Reader
{
    public static function readFromJson(string $json, string $baseType = OpenApi::class): SpecBaseObject
    {
        return new $baseType(json_decode($json, true));
    }

    public static function readFromYaml(string $yaml, string $baseType = OpenApi::class): SpecBaseObject
    {
        return new $baseType(Yaml::parse($yaml));
    }
}
