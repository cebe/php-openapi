<?php

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * An object representing a Server.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#serverObject
 *
 * @property-read string $url
 * @property-read string $description
 * @property-read ServerVariable[] $variables
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Server extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'url' => Type::STRING,
            'description' => Type::STRING,
            'variables' => [Type::STRING, ServerVariable::class],
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
        $this->requireProperties(['url']);
    }
}
