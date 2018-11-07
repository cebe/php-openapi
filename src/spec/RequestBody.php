<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Describes a single request body.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#requestBodyObject
 *
 * @property-read string $description
 * @property-read MediaType[] $content
 * @property-read string $required
 *
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
     */
    protected function performValidation()
    {
        $this->requireProperties(['content']);
    }
}
