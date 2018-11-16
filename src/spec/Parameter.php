<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\exceptions\TypeErrorException;
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
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     * @throws TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data)
    {
        if (!isset($data['style']) && isset($data['in'])) {
            // Spec: Default values (based on value of in):
            // for query - form;
            // for path - simple;
            // for header - simple;
            // for cookie - form.
            switch ($data['in']) {
                case 'query':
                case 'cookie':
                    $data['style'] = 'form';
                    break;
                case 'path':
                case 'header':
                    $data['style'] = 'simple';
                    break;
            }
        }
        if (!isset($data['explode']) && isset($data['style'])) {
            // Spec: When style is form, the default value is true. For all other styles, the default value is false.
            $data['explode'] = ($data['style'] === 'form');
        }
        parent::__construct($data);
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
            $this->addError("A Parameter Object MUST contain either a schema property, or a content property, but not both. ");
        }
    }
}
