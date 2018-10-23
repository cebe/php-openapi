<?php

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * An object representing a Server Variable for server URL template substitution.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#serverVariableObject
 *
 * @property-read string[] $enum
 * @property-read string $default
 * @property-read string $description
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class ServerVariable extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'enum' => ['string'],
            'default' => 'string',
            'description' => 'string',
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
        $this->requireProperties(['default']);
    }
}
