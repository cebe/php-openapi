<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Allows configuration of the supported OAuth Flows.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#oauthFlowsObject
 *
 * @property OAuthFlow|null $implicit
 * @property OAuthFlow|null $password
 * @property OAuthFlow|null $clientCredentials
 * @property OAuthFlow|null $authorizationCode
 */
class OAuthFlows extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'implicit' => OAuthFlow::class,
            'password' => OAuthFlow::class,
            'clientCredentials' => OAuthFlow::class,
            'authorizationCode' => OAuthFlow::class,
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
    }
}
