<?php

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Allows referencing an external resource for extended documentation.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#externalDocumentationObject
 *
 * @property-read string $description
 * @property-read string $url
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class ExternalDocumentation extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'description' => 'string',
            'url' => 'string',
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
        $this->validateUrl('url');
    }
}
