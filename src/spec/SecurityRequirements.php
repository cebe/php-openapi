<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Lists the required security schemes to execute this operation.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#securityRequirementObject
 * TODO docs
 */
class SecurityRequirements extends SpecBaseObject
{
    private $_securityRequirements;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->_securityRequirements = $data;
    }

    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
//        (Type::STRING => Type::ANY)[]

        // this object does not have a fixed set of attribute names
        return [];
//        return [Type::STRING, SecurityRequirement::class];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    protected function performValidation()
    {
    }

    /**
     * @return mixed returns the serializable data of this object for converting it
     * to JSON or YAML.
     * TODO
     */
    public function getSerializableData()
    {
        $data = [];
        foreach ($this->_securityRequirements as $name => $securityRequirement) {
            /** @var SecurityRequirement $securityRequirement */
            $data[] = [$name => json_decode(json_encode($securityRequirement->getSerializableData()), true)];
        }
        return $data;
    }
}
