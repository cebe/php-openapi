<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

use cebe\openapi\spec\OpenApi;
use Symfony\Component\Yaml\Yaml;

/**
 *
 *
 */
class Reader
{
    public static function readFromJson(string $json, string $baseType = OpenApi::class): SpecObjectInterface
    {
        return new $baseType(json_decode($json, true));
    }

    public static function readFromYaml(string $yaml, string $baseType = OpenApi::class): SpecObjectInterface
    {
        return new $baseType(Yaml::parse($yaml));
    }
}
