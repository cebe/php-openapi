<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\SpecBaseObject;

/**
 * This is the root document object of the OpenAPI document.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#openapi-object
 *
 * @property-read string $openapi
 * @property-read Info $info
 * @property-read Server[] $servers
 * @property-read Paths|PathItem[] $paths
 * @property-read Components|null $components
 * @property-read SecurityRequirement[] $security
 * @property-read Tag[] $tags
 * @property-read ExternalDocumentation|null $externalDocs
 *
 */
class OpenApi extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'openapi' => Type::STRING,
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
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     * @throws TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data)
    {
        if (empty($data['servers'])) {
            // Spec: If the servers property is not provided, or is an empty array, the default value would be a Server Object with a url value of /.
            $data['servers'] = [
                ['url' => '/'],
            ];
        }
        parent::__construct($data);
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    public function performValidation()
    {
        $this->requireProperties(['openapi', 'info', 'paths']);
        if (!empty($this->openapi) && !preg_match('/^3\.0\.\d+$/', $this->openapi)) {
            $this->addError('Unsupported openapi version: ' . $this->openapi);
        }
    }
}
