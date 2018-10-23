<?php

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * This is the root document object of the OpenAPI document.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#openapi-object
 *
 * @property-read string $openapi
 * @property-read Info $info
 * @property-read Server[] $servers
 * @property-read Paths $paths
 * @property-read Components|null $components
 * @property-read SecurityRequirement[] $security
 * @property-read Tag[] $tags
 * @property-read ExternalDocumentation|null $externalDocs
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class OpenApi extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'openapi' => 'string',
            'info' => Info::class,
            'servers' => [Server::class],
            'paths' => Paths::class,
            'components' => Components::class,
            'security' => [SecurityRequirement::class],
            'tags' => [Tag::class],
            'externalDocs' => ExternalDocumentation::class,
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    public function performValidation()
    {
        $this->requireProperties(['openapi', 'info', 'paths']);
        if (!empty($this->openapi) && !preg_match('/^3\.0\.\d+$/', $this->openapi)) {
            $this->addError('Unsupported openapi version: ' . $this->openapi);
        }
    }
}
