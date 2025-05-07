<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * A required security scheme to execute this operation.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#securityRequirementObject
 *
 */
class SecurityRequirement extends SpecBaseObject
{
    private $_securityRequirement;
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->_securityRequirement = $data;
    }

    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        // this object does not have a fixed set of attribute names
        return [];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
    }

    public function getSerializableData()
    {
        return $this->_securityRequirement;
    }
}
