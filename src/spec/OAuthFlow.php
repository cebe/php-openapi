<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Configuration details for a supported OAuth Flow.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#oauthFlowObject
 *
 * @property string $authorizationUrl
 * @property string $tokenUrl
 * @property string $refreshUrl
 * @property string[] $scopes
 */
class OAuthFlow extends SpecBaseObject
{

    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'authorizationUrl' => Type::STRING,
            'tokenUrl' => Type::STRING,
            'refreshUrl' => Type::STRING,
            'scopes' => [Type::STRING, Type::STRING],
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
        $this->requireProperties(['scopes']);
        // TODO: Validation in context of the parent object
        // authorizationUrl is required if this object is in "implicit", "authorizationCode"
        // tokenUrl is required if this object is in "password", "clientCredentials", "authorizationCode"
    }
}
