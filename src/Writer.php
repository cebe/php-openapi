<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

use cebe\openapi\exceptions\IOException;
use cebe\openapi\spec\OpenApi;

/**
 * Utility class to simplify writing JSON or YAML OpenAPI specs.
 *
 */
class Writer
{
    /**
     * Convert OpenAPI spec object to JSON data.
     * @param SpecObjectInterface|OpenApi $object the OpenApi object instance.
     * @param int $flags json_encode() flags. Parameter available since version 1.7.0.
     * @return string JSON string.
     */
    public static function writeToJson(SpecObjectInterface $object, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($object->getSerializableData(), $flags);
    }

    /**
     * Convert OpenAPI spec object to YAML data.
     * @param SpecObjectInterface|OpenApi $object the OpenApi object instance.
     * @return string YAML string.
     */
    public static function writeToYaml(SpecObjectInterface $object): string
    {
//        return Yaml::dump($object->getSerializableData(), 256, 2, Yaml::DUMP_OBJECT_AS_MAP | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
        return yaml_emit(
            json_decode(json_encode($object->getSerializableData()), true)
        );
    }

    /**
     * Write OpenAPI spec object to JSON file.
     * @param SpecObjectInterface|OpenApi $object the OpenApi object instance.
     * @param string $fileName file name to write to.
     * @throws IOException when writing the file fails.
     */
    public static function writeToJsonFile(SpecObjectInterface $object, string $fileName): void
    {
        if (file_put_contents($fileName, static::writeToJson($object)) === false) {
            throw new IOException("Failed to write file: '$fileName'");
        }
    }

    /**
     * Write OpenAPI spec object to YAML file.
     * @param SpecObjectInterface|OpenApi $object the OpenApi object instance.
     * @param string $fileName file name to write to.
     * @throws IOException when writing the file fails.
     */
    public static function writeToYamlFile(SpecObjectInterface $object, string $fileName): void
    {
        if (file_put_contents($fileName, static::writeToYaml($object)) === false) {
            throw new IOException("Failed to write file: '$fileName'");
        }
    }
}
