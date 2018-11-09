<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * A single encoding definition applied to a single schema property.
 *
 * @property-read string $contentType
 * @property-read Header[]|Reference[] $headers
 * @property-read string $style
 * @property-read boolean $explode
 * @property-read boolean $allowReserved
 */
class Encoding extends SpecBaseObject
{

    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'contentType' => Type::STRING,
            'headers' => [Type::STRING, Header::class],
            'style' => Type::STRING,
            'explode' => Type::BOOLEAN,
            'allowReserved' => Type::BOOLEAN,
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
    }
}
