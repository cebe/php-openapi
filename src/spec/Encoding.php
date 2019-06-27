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
 * @property string $contentType
 * @property Header[]|Reference[] $headers
 * @property string $style
 * @property boolean $explode
 * @property boolean $allowReserved
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
            // TODO implement default values for style
            // https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#encodingObject
            'style' => Type::STRING,
            'explode' => Type::BOOLEAN,
            'allowReserved' => Type::BOOLEAN,
        ];
    }

    private $_attributeDefaults = [];

    /**
     * @return array array of attributes default values.
     */
    protected function attributeDefaults(): array
    {
        return $this->_attributeDefaults;
    }

    /**
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     * @throws TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data, ?Schema $schema = null)
    {
        if (isset($data['style'])) {
            // Spec: When style is form, the default value is true.
            $this->_attributeDefaults['explode'] = ($data['style'] === 'form');
        }
        if ($schema !== null) {
            // Spec: Default value depends on the property type:
            // for string with format being binary – application/octet-stream;
            // for other primitive types – text/plain;
            // for object - application/json;
            // for array – the default is defined based on the inner type.
            switch ($schema->type === 'array' ? ($schema->items->type ?? 'array') : $schema->type) {
                case Type::STRING:
                    if ($schema->format === 'binary') {
                        $this->_attributeDefaults['contentType'] = 'application/octet-stream';
                        break;
                    }
                    // no break here
                case Type::BOOLEAN:
                case Type::INTEGER:
                case Type::NUMBER:
                    $this->_attributeDefaults['contentType'] = 'text/plain';
                    break;
                case 'object':
                    $this->_attributeDefaults['contentType'] = 'application/json';
                    break;
            }
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
