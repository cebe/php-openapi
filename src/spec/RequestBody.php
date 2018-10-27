<?php

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Describes a single request body.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#requestBodyObject
 *
 * @property-read string $description
 * @property-read array $content
 * @property-read string $required
 * 
 * @author Carsten Brandt <mail@cebe.cc>
 * 
 * @TODO: Unsure how to define the content attribtue defintion and validation defintion.:
 * > REQUIRED. The content of the request body. The key is a media type or media type range and the value describes it.
 * > For requests that match multiple keys, only the most specific key is applicable. e.g. text/plain overrides text/*
 */
class RequestBody
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'description' => Type::STRING,
            'content' => [Type::STRING, MediaType::class],
            'required' => Type::BOOLEAN,
        ];
    }
     /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
        $this->requireProperties(['content']);
    }
}
