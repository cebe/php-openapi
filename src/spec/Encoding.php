<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\SpecBaseObject;

/**
 * A single encoding definition applied to a single schema property.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#encodingObject
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
            // TODO implement default values for contentType
            // https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#encodingObject
            'contentType' => Type::STRING,
            'headers' => [Type::STRING, Header::class],
            // TODO implement default values for style
            // https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#encodingObject
            'style' => Type::STRING,
            'explode' => Type::BOOLEAN,
            'allowReserved' => Type::BOOLEAN,
        ];
    }

    /**
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     * @throws TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data)
    {
        if (!isset($data['explode']) && isset($data['style'])) {
            // Spec: When style is form, the default value is true.
            $data['explode'] = ($data['style'] === 'form');
        }
        parent::__construct($data);
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
    }
}
