<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Lists the required security requirement to execute this operation.
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

        $read = true;
        foreach ($data as $index => $value) {
            if (is_numeric($index)) { // read
                $requirements = $value;
                $this->_securityRequirements[array_keys($value)[0]] = new SecurityRequirement(array_values($value)[0]);
            } else { // write
                $read = false;
                $requirements = $data;
                $this->_securityRequirements[$index] = $value;
            }
        }
        if ($data === []) {
            $this->_securityRequirements = [];
        }
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

    /**
     * @return mixed returns the serializable data of this object for converting it
     * to JSON or YAML.
     * TODO
     */
    public function getSerializableData()
    {
        $data = [];
        foreach ($this->_securityRequirements ?? [] as $name => $securityRequirement) {
            /** @var SecurityRequirement $securityRequirement */
            $data[] = [$name => $securityRequirement->getSerializableData()];
        }
        return $data;
    }

    public function getRequirement(string $name)
    {
        return $this->_securityRequirements[$name] ?? null;
    }

    public function getRequirements()
    {
        return $this->_securityRequirements;
    }
}
