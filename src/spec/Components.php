<?php

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
 * @property-read Schema[]|Reference[] $schemas
 * @property-read Response[]|Reference[] $responses
 * @property-read Parameter[]|Reference[] $parameters
 * @property-read Example[]|Reference[] $examples
 * @property-read RequestBody[]|Reference[] $requestBodies
 * @property-read Header[]|Reference[] $headers
 * @property-read SecurityScheme[]|Reference[] $securitySchemes
 * @property-read Link[]|Reference[] $links
 * @property-read Callback[]|Reference[] $callbacks
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Components extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'schemas' => [Schema::class],// TODO implement support for reference
            'responses' => [Response::class],
            'parameters' => [Parameter::class],
            'examples' => [Example::class],
            'requestBodies' => [RequestBody::class],
            'headers' => [Header::class],
            'securitySchemes' => [SecurityScheme::class],
            'links' => [Link::class],
            'callbacks' => [Callback::class],
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
        // All the fixed fields declared above are objects that MUST use keys that match the regular expression: ^[a-zA-Z0-9\.\-_]+$.
        foreach (array_keys($this->attributes()) as $attribute) {
            foreach($this->$attribute as $k => $v) {
                if (!preg_match('~^[a-zA-Z0-9\.\-_]+$~', $k)) {
                    $this->addError("Invalid key '$k' used in Components Object for attribute '$attribute', does not match ^[a-zA-Z0-9\.\-_]+\$.");
                }
            }
        }
    }
}
