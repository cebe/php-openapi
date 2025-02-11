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
 * @property string $openapi
 * @property Info $info
 * @property Server[] $servers
 * @property Paths|PathItem[] $paths
 * @property Components|null $components
 * @property SecurityRequirement[] $security
 * @property Tag[] $tags
 * @property ExternalDocumentation|null $externalDocs
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
     * @param string $name  name of security attribute requirement
     */
    public function removeSecurity(string $name): void
    {
        $this->deleteProperty('security', $name);
    }
    
    /**
     * @param string $name tag name
     */
    public function removeTag(string $name): void
    {
        $this->deleteProperty('tags', $name);
    }
    
    /**
     * @param string $url  server's url value
     */
    public function removeServer(string $url): void
    {
        $this->deleteProperty('servers', 'url', $url);
    }

    /**
     * @return array array of attributes default values.
     */
    protected function attributeDefaults(): array
    {
        return [
            // Spec: If the servers property is not provided, or is an empty array,
            // the default value would be a Server Object with a url value of /.
            'servers' => [
                new Server(['url' => '/'])
            ],
        ];
    }

    public function __get($name)
    {
        $ret = parent::__get($name);
        // Spec: If the servers property is not provided, or is an empty array,
        // the default value would be a Server Object with a url value of /.
        if ($name === 'servers' && $ret === []) {
            return $this->attributeDefaults()['servers'];
        }
        return $ret;
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    public function performValidation()
    {
        $this->requireProperties(['openapi', 'info', 'paths']);
        if (!empty($this->openapi) && !preg_match('/^3\.0\.\d+(-rc\d)?$/i', $this->openapi)) {
            $this->addError('Unsupported openapi version: ' . $this->openapi);
        }
    }
}
