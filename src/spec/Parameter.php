<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Describes a single operation parameter.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#parameterObject
 *
 * @property-read string $name
 * @property-read string $in
 * @property-read string $description
 * @property-read bool $required
 * @property-read bool $deprecated
 * @property-read bool $allowEmptyValue
 *
 * TODO implement more
 */
class Parameter extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'name' => Type::STRING,
            'in' => Type::STRING,
            'description' => Type::STRING,
            'required' => Type::BOOLEAN,
            'deprecated' => Type::BOOLEAN,
            'allowEmptyValue' => Type::BOOLEAN,
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
        // TODO: Implement performValidation() method.
    }
}
