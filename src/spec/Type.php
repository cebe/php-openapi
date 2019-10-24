<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

/**
 * Data Types
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#dataTypes
 */
class Type
{
    const ANY = 'any';
    const INTEGER = 'integer';
    const NUMBER = 'number';
    const STRING = 'string';
    const BOOLEAN = 'boolean';
    const OBJECT = 'object';
    const ARRAY = 'array';

    /**
     * Indicate whether a type is a scalar type, i.e. not an array or object.
     *
     * For ANY this will return false.
     *
     * @param string $type value from one of the type constants defined in this class.
     * @return bool whether the type is a scalar type.
     * @since 1.2.1
     */
    public static function isScalar(string $type): bool
    {
        return in_array($type, [
            self::INTEGER,
            self::NUMBER,
            self::STRING,
            self::BOOLEAN,
        ]);
    }
}
