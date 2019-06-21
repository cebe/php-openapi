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
 * @property string $description
 * @property MediaType[] $content
 * @property boolean $required
 */
class RequestBody extends SpecBaseObject
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
