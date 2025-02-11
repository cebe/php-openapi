<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Describes a single response from an API Operation, including design-time, static links to operations based on the response.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#responseObject
 *
 * @property string $description
 * @property Header[]|Reference[] $headers
 * @property MediaType[] $content
 * @property Link[]|Reference[] $links
 */
class Response extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'description' => Type::STRING,
            'headers' => [Type::STRING, Header::class],
            'content' => [Type::STRING, MediaType::class],
            'links' => [Type::STRING, Link::class],
        ];
    }

    /**
     * @param string $name  headers' name
     */
    public function removeHeader(string $name): void
    {
        $this->deleteProperty('headers', $name);
    }

    /**
     * @param string $operationId  headers' operationId
     */
    public function removeLinkByOperationId(string $operationId): void
    {
        $this->deleteProperty('links', 'operationId', $operationId);
    }

    /**
     * @param string $operationRef  link's operationRef
     */
    public function removeLinkByoperationRef(string $operationRef): void
    {
        $this->deleteProperty('links', 'operationRef', $operationRef);
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
        $this->requireProperties(['description']);
    }
}
