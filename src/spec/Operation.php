<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Describes a single API operation on a path.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#operationObject
 *
 * @property-read string[] $tags
 * @property-read string $summary
 * @property-read string $description
 * @property-read ExternalDocumentation|null $externalDocs
 * @property-read string $operationId
 * @property-read Parameter[]|Reference[] $parameters
 * @property-read RequestBody|Reference $requestBody
 * @property-read Responses $responses
 * @property-read Callback[]|Reference[] $callbacks
 * @property-read bool $deprecated
 * @property-read SecurityRequirement[] $security
 * @property-read Server[] $servers
 */
class Operation extends SpecBaseObject
{

    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'tags' => [Type::STRING],
            'summary' => Type::STRING,
            'description' => Type::STRING,
            'externalDocs' => ExternalDocumentation::class,
            'operationId' => Type::STRING,
            'parameters' => [Parameter::class],
            'requestBody' => RequestBody::class,
            'responses' => Responses::class,
            'callbacks' => [Type::STRING, Callback::class],
            'deprecated' => Type::BOOLEAN,
            'security' => [SecurityRequirement::class],
            'servers' => [Server::class],
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
        $this->requireProperties(['responses']);
    }
}
