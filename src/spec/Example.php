<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Example Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#exampleObject
 *
 * @property string $summary
 * @property string $description
 * @property mixed $value
 * @property string $externalValue
 */
class Example extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'summary' => Type::STRING,
            'description' => Type::STRING,
            'value' => Type::ANY,
            'externalValue' => Type::STRING,
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
    }
}
