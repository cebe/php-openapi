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
 * @property string[] $tags
 * @property string $summary
 * @property string $description
 * @property ExternalDocumentation|null $externalDocs
 * @property string $operationId
 * @property Parameter[]|Reference[] $parameters
 * @property RequestBody|Reference|null $requestBody
 * @property Responses|Response[]|null $responses
 * @property Callback[]|Reference[] $callbacks
 * @property bool $deprecated
 * @property SecurityRequirement[] $security
 * @property Server[] $servers
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

    protected function attributeDefaults(): array
    {
        return [
            'security' => null,
        ];
    }

    /**
     * @param string $name tag name
     */
    public function removeTag(string $name): void
    {
        $this->deleteProperty('tags', $name);
    }
    
    /**
     * @param string $name  parameter's property key
     */
    public function removeParameter(string $name): void
    {
        $this->deleteProperty('parameters', 'name', $name);
    }
    
    /**
     * @param string $name  name of security requirement
     */
    public function removeSecurity(string $name): void
    {
        $this->deleteProperty('security', $name);
    }

    /**
     * @param string $name  server's property key
     */
    public function removeServer(string $name): void
    {
        $this->deleteProperty('servers', 'url', $name);
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
