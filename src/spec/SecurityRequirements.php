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
 *
 */
class SecurityRequirements extends SpecBaseObject
{
    private $_securityRequirements;

    public function __construct(array $data)
    {
        parent::__construct($data);

        foreach ($data as $index => $value) {
            if (is_numeric($index) && $value === []) { # empty Security Requirement Object (`{}`) = anonymous access
                $this->_securityRequirements[$index] = [];
                continue;
            }

            if (is_string($index)) {
                $this->_securityRequirements[] = [$index => $value instanceof SecurityRequirement ? $value : new SecurityRequirement($value)];
            } elseif (is_numeric($index)) {
                foreach ($value as $innerIndex => $subValue) {
                    $this->_securityRequirements[$index][$innerIndex] = $subValue instanceof SecurityRequirement ? $subValue : new SecurityRequirement($subValue);
                }
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
     * {@inheritDoc}
     */
    public function getSerializableData()
    {
        $data = [];

        foreach ($this->_securityRequirements ?? [] as $outerIndex => $content) {
            if (is_string($outerIndex)) {
                $data[] = [$outerIndex => $content->getSerializableData()];
            } elseif (is_numeric($outerIndex)) {
                if ($content === []) {
                    $data[$outerIndex] = (object)$content;
                    continue;
                }
                $innerResult = [];
                foreach ($content as $innerIndex => $innerContent) {
                    $result = is_object($innerContent) && method_exists($innerContent, 'getSerializableData') ? $innerContent->getSerializableData() : $innerContent;
                    $innerResult[$innerIndex] = $result;
                }
                $data[$outerIndex] = (object)$innerResult;
            }
        }
        return $data;
    }

    public function getRequirement(string $name)
    {
        return static::searchKey($this->_securityRequirements, $name);
    }

    public function getRequirements()
    {
        return $this->_securityRequirements;
    }

    private static function searchKey(array $array, string $searchKey)
    {
        foreach ($array as $key => $value) {
            if ($key === $searchKey) {
                return $value;
            }
            if (is_array($value)) {
                $mt = __METHOD__;
                $result = $mt($value, $searchKey);
                if ($result !== null) {
                    return $result; // key found in deeply nested/associative array
                }
            }
        }
        return null; // key not found
    }
}
