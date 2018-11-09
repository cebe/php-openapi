<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Describes a single operation parameter.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#parameterObject
 *
 * @property-read string $name
 * @property-read string $in
 * @property-read string $description
 * @property-read bool $required
 * @property-read bool $deprecated
 * @property-read bool $allowEmptyValue
 *
 * @property-read string $style
 * @property-read boolean $explode
 * @property-read boolean $allowReserved
 * @property-read Schema|Reference|null $schema
 * @property-read mixed $example
 * @property-read Example[] $examples
 *
 * @property-read MediaType[] $content
 */
class Parameter extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'name' => Type::STRING,
            'in' => Type::STRING,
            'description' => Type::STRING,
            'required' => Type::BOOLEAN,
            'deprecated' => Type::BOOLEAN,
            'allowEmptyValue' => Type::BOOLEAN,

            'style' => Type::STRING,
            'explode' => Type::BOOLEAN,
            'allowReserved' => Type::BOOLEAN,
            'schema' => Schema::class,
            'example' => Type::ANY,
            'examples' => [Type::STRING, Example::class],

            'content' => [Type::STRING, MediaType::class],
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
        $this->requireProperties(['name', 'in']);
        if ($this->in === 'path') {
            $this->requireProperties(['required']);
            if (!$this->required) {
                $this->addError("Parameter 'required' must be true for 'in': 'path'.");
            }
        }
        if (!empty($this->content) && !empty($this->schema)) {
            $this->addError("A parameter MUST contain either a schema property, or a content property, but not both. ");
        }
    }
}
