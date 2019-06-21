<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * License information for the exposed API.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#licenseObject
 *
 * @property string $name
 * @property string $url
 *
 */
class License extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'name' => Type::STRING,
            'url' => Type::STRING,
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
        $this->requireProperties(['name']);
        $this->validateUrl('url');
    }
}
