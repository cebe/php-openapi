<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Adds metadata to a single tag that is used by the Operation Object.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#tagObject
 *
 * @property string $name
 * @property string $description
 * @property ExternalDocumentation|null $externalDocs
 *
 */
class Tag extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'name' => Type::STRING,
            'description' => Type::STRING,
            'externalDocs' => ExternalDocumentation::class,
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
        $this->requireProperties(['name']);
    }
}
