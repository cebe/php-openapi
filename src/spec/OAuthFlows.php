<?php
/**
 * Created by PhpStorm.
 * User: cebe
 * Date: 07.11.18
 * Time: 22:01
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Allows configuration of the supported OAuth Flows.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#oauthFlowsObject
 *
 * @property-read OAuthFlow|null $implicit
 * @property-read OAuthFlow|null $password
 * @property-read OAuthFlow|null $clientCredentials
 * @property-read OAuthFlow|null $authorizationCode
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