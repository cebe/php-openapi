<?php

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Adds metadata to a single tag that is used by the Operation Object.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#tagObject
 *
 * @property-read string $name
 * @property-read string $description
 * @property-read ExternalDocumentation $externalDocs
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Tag extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'name' => 'string',
            'description' => 'string',
            'externalDocs' => ExternalDocumentation::class,
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
        $this->requireProperties(['name']);
    }
}
