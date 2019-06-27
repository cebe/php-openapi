<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Holds a set of reusable objects for different aspects of the OAS.
 *
 * All objects defined within the components object will have no effect on the API unless they are explicitly referenced
 * from properties outside the components object.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#componentsObject
 *
 * @property Schema[]|Reference[] $schemas
 * @property Response[]|Reference[] $responses
 * @property Parameter[]|Reference[] $parameters
 * @property Example[]|Reference[] $examples
 * @property RequestBody[]|Reference[] $requestBodies
 * @property Header[]|Reference[] $headers
 * @property SecurityScheme[]|Reference[] $securitySchemes
 * @property Link[]|Reference[] $links
 * @property Callback[]|Reference[] $callbacks
 *
 *
 */
class Components extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'schemas' => [Type::STRING, Schema::class],
            'responses' => [Type::STRING, Response::class],
            'parameters' => [Type::STRING, Parameter::class],
            'examples' => [Type::STRING, Example::class],
            'requestBodies' => [Type::STRING, RequestBody::class],
            'headers' => [Type::STRING, Header::class],
            'securitySchemes' => [Type::STRING, SecurityScheme::class],
            'links' => [Type::STRING, Link::class],
            'callbacks' => [Type::STRING, Callback::class],
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
        // All the fixed fields declared above are objects that MUST use keys that match the regular expression: ^[a-zA-Z0-9\.\-_]+$.
        foreach (array_keys($this->attributes()) as $attribute) {
            if (is_array($this->$attribute)) {
                foreach ($this->$attribute as $k => $v) {
                    if (!preg_match('~^[a-zA-Z0-9\.\-_]+$~', $k)) {
                        $this->addError("Invalid key '$k' used in Components Object for attribute '$attribute', does not match ^[a-zA-Z0-9\.\-_]+\$.");
                    }
                }
            }
        }
    }
}
