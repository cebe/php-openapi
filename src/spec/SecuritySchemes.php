<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

class SecuritySchemes extends SpecBaseObject
{
    private $_securitySchemes;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->_securitySchemes = $data;
    }

    protected function attributes(): array
    {
        return [];
    }

    public function performValidation()
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
        foreach ($this->_securitySchemes as $name => $securityScheme) {
            /** @var SecurityScheme $securityScheme */
            $data[$name] = $securityScheme->getSerializableData();
        }
        return (object) $data;
    }
}
