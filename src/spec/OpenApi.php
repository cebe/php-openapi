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
 * @property PathItem[]|null $webhooks
 * @property SecurityRequirement[] $security
 * @property Tag[] $tags
 * @property ExternalDocumentation|null $externalDocs
 *
 */
class OpenApi extends SpecBaseObject
{
    const VERSION_3_0 = '3.0';
    const VERSION_3_1 = '3.1';
    const VERSION_UNSUPPORTED = 'unsupported';

    /**
     * Pattern used to validate OpenAPI versions.
     */
    const PATTERN_VERSION = '/^(3\.(0|1))\.\d+(-rc\d)?$/i';

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
            'webhooks' => [PathItem::class],
            'components' => Components::class,
            'security' => [SecurityRequirement::class],
            'tags' => [Tag::class],
            'externalDocs' => ExternalDocumentation::class,
        ];
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
        if ($this->getMajorVersion() === static::VERSION_3_0) {
            $this->requireProperties(['openapi', 'info', 'paths']);
        } else {
            $this->requireProperties(['openapi', 'info'], ['paths', 'webhooks', 'components']);
        }

        if (!empty($this->openapi) && !preg_match(static::PATTERN_VERSION, $this->openapi)) {
            $this->addError('Unsupported openapi version: ' . $this->openapi);
        }
    }

    /**
     * Returns the OpenAPI major version of the loaded OpenAPI description.
     * @return string This returns a value of one of the `VERSION_*`-constants. Currently supported versions are:
     *
     * - `VERSION_3_0 = '3.0'`
     * - `VERSION_3_1 = '3.1'`
     *
     * For unsupported version, this function will return `VERSION_UNSUPPORTED = 'unsupported'`
     */
    public function getMajorVersion()
    {
        if (empty($this->openapi)) {
            return self::VERSION_UNSUPPORTED;
        }
        if (preg_match(static::PATTERN_VERSION, $this->openapi, $matches)) {
            switch ($matches[1]) {
                case '3.0':
                    return static::VERSION_3_0;
                case '3.1':
                    return static::VERSION_3_1;
            }
        }

        return self::VERSION_UNSUPPORTED;
    }
}
