<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\spec\OpenApi;
use Symfony\Component\Yaml\Yaml;

/**
 * Utility class to simplify reading JSON or YAML OpenAPI specs.
 *
 */
class Reader
{
    /**
     * Populate OpenAPI spec object from JSON data.
     * @param string $json the JSON string to decode.
     * @param string $baseType the base Type to instantiate. This must be an instance of [[SpecObjectInterface]].
     * The default is [[OpenApi]] which is the base type of a OpenAPI specification file.
     * You may choose a different type if you instantiate objects from sub sections of a specification.
     * @return SpecObjectInterface|OpenApi the OpenApi object instance.
     * @throws TypeErrorException in case invalid spec data is supplied.
     */
    public static function readFromJson(string $json, string $baseType = OpenApi::class): SpecObjectInterface
    {
        return new $baseType(json_decode($json, true));
    }

    /**
     * Populate OpenAPI spec object from YAML data.
     * @param string $yaml the YAML string to decode.
     * @param string $baseType the base Type to instantiate. This must be an instance of [[SpecObjectInterface]].
     * The default is [[OpenApi]] which is the base type of a OpenAPI specification file.
     * You may choose a different type if you instantiate objects from sub sections of a specification.
     * @return SpecObjectInterface|OpenApi the OpenApi object instance.
     * @throws TypeErrorException in case invalid spec data is supplied.
     */
    public static function readFromYaml(string $yaml, string $baseType = OpenApi::class): SpecObjectInterface
    {
        return new $baseType(Yaml::parse($yaml));
    }

    /**
     * Populate OpenAPI spec object from a JSON file.
     * @param string $fileName the file name of the file to be read.
     * If `$resolveReferences` is true (the default), this should be an absolute URL, a `file://` URI or
     * an absolute path to allow resolving relative path references.
     * @param string $baseType the base Type to instantiate. This must be an instance of [[SpecObjectInterface]].
     * The default is [[OpenApi]] which is the base type of a OpenAPI specification file.
     * You may choose a different type if you instantiate objects from sub sections of a specification.
     * @param bool $resolveReferences whether to automatically resolve references in the specification.
     * If `true`, all [[Reference]] objects will be replaced with their referenced spec objects by calling
     * [[SpecObjectInterface::resolveReferences()]].
     * @return SpecObjectInterface|OpenApi the OpenApi object instance.
     * @throws TypeErrorException in case invalid spec data is supplied.
     * @throws UnresolvableReferenceException in case references could not be resolved.
     */
    public static function readFromJsonFile(string $fileName, string $baseType = OpenApi::class, $resolveReferences = true): SpecObjectInterface
    {
        $spec = static::readFromJson(file_get_contents($fileName), $baseType);
        $spec->setReferenceContext(new ReferenceContext($spec, $fileName));
        if ($resolveReferences) {
            $spec->resolveReferences();
        }
        return $spec;
    }

    /**
     * Populate OpenAPI spec object from YAML file.
     * @param string $fileName the file name of the file to be read.
     * If `$resolveReferences` is true (the default), this should be an absolute URL, a `file://` URI or
     * an absolute path to allow resolving relative path references.
     * @param string $baseType the base Type to instantiate. This must be an instance of [[SpecObjectInterface]].
     * The default is [[OpenApi]] which is the base type of a OpenAPI specification file.
     * You may choose a different type if you instantiate objects from sub sections of a specification.
     * @param bool $resolveReferences whether to automatically resolve references in the specification.
     * If `true`, all [[Reference]] objects will be replaced with their referenced spec objects by calling
     * [[SpecObjectInterface::resolveReferences()]].
     * @return SpecObjectInterface|OpenApi the OpenApi object instance.
     * @throws TypeErrorException in case invalid spec data is supplied.
     * @throws UnresolvableReferenceException in case references could not be resolved.
     */
    public static function readFromYamlFile(string $fileName, string $baseType = OpenApi::class, $resolveReferences = true): SpecObjectInterface
    {
        $spec = static::readFromYaml(file_get_contents($fileName), $baseType);
        $spec->setReferenceContext(new ReferenceContext($spec, $fileName));
        if ($resolveReferences) {
            $spec->resolveReferences();
        }
        return $spec;
    }
}
