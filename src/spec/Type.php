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
}
