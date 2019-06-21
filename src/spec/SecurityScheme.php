<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Defines a security scheme that can be used by the operations.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#securitySchemeObject
 *
 * @property string $type
 * @property string $description
 * @property string $name
 * @property string $in
 * @property string $scheme
 * @property string $bearerFormat
 * @property OAuthFlows|null $flows
 * @property string $openIdConnectUrl
 */
class SecurityScheme extends SpecBaseObject
{
    private $knownTypes = [
        "apiKey",
        "http",
        "oauth2",
        "openIdConnect"
    ];

    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'type' => Type::STRING,
            'description' => Type::STRING,
            'name' => Type::STRING,
            'in' => Type::STRING,
            'scheme' => Type::STRING,
            'bearerFormat' => Type::STRING,
            'flows' => OAuthFlows::class,
            'openIdConnectUrl' => Type::STRING,
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
        $this->requireProperties(['type']);
        if (isset($this->type)) {
            if (!in_array($this->type, $this->knownTypes)) {
                $this->addError("Unknown Security Scheme type: $this->type");
            } else {
                switch ($this->type) {
                    case "apiKey":
                        $this->requireProperties(['name', 'in']);
                        if (isset($this->in)) {
                            if (!in_array($this->in, ["query", "header", "cookie"])) {
                                $this->addError("Invalid value for Security Scheme property 'in': $this->in");
                            }
                        }
                        break;
                    case "http":
                        $this->requireProperties(['scheme']);
                        break;
                    case "oauth2":
                        $this->requireProperties(['flows']);
                        break;
                    case "openIdConnect":
                        $this->requireProperties(['openIdConnectUrl']);
                        break;
                }
            }
        }
    }
}
