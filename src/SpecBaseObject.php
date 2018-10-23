<?php

namespace cebe\openapi;

use cebe\openapi\exceptions\ReadonlyPropertyException;
use cebe\openapi\exceptions\UnknownPropertyException;

/**
 * Base class for all spec objects.
 *
 * Implements property management and validation basics.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
abstract class SpecBaseObject
{
    private $_properties = [];
    private $_errors = [];

    /**
     * @return array array of attributes available in this object.
     */
    abstract protected function attributes(): array;

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     *
     * Call `addError()` in case of validation errors.
     */
    abstract protected function performValidation();

    /**
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     */
    public function __construct(array $data)
    {
        foreach($this->attributes() as $property => $type) {
            if (!isset($data[$property])) {
                continue;
            }

            if ($type === 'string') {
                $this->_properties[$property] = $data[$property];
            } elseif (is_array($type)) {
                if (!is_array($data[$property])) {
                    $this->_errors[] = "property '$property' must be array, but " . gettype($data[$property]) . " given.";
                    continue;
                }
                switch (count($type)) {
                    case 1:
                        // array
                        $this->_properties[$property] = [];
                        foreach($data[$property] as $item) {
                            if ($type[0] === 'string') {
                                if (!is_string($item)) {
                                    $this->_errors[] = "property '$property' must be array of strings, but array has " . gettype($item) . " element.";
                                }
                                $this->_properties[$property][] = $item;
                            } else {
                                $this->_properties[$property][] = new $type[0]($item);
                            }
                        }
                        break;
                    case 2:
                        // map
                        if ($type[0] !== 'string') {
                            throw new \Exception('Invalid map key type: ' . $type[0]);
                        }
                        $this->_properties[$property] = [];
                        foreach($data[$property] as $key => $item) {
                            if ($type[1] === 'string') {
                                if (!is_string($item)) {
                                    $this->_errors[] = "property '$property' must be map<string, string>, but entry '$key' is of type " . gettype($item) . ".";
                                }
                                $this->_properties[$property][$key] = $item;
                            } else {
                                $this->_properties[$property][$key] = new $type[1]($item);
                            }
                        }
                        break;
                }
            } else {
                $this->_properties[$property] = new $type($data[$property]);
            }
            unset($data[$property]);
        }
        foreach($data as $additionalProperty => $value) {
            $this->_properties[$additionalProperty] = $value;
        }
    }

    /**
     * Validate object data according to OpenAPI spec.
     * @return bool whether the loaded data is valid according to OpenAPI spec
     * @see getErrors()
     */
    public function validate(): bool
    {
        foreach($this->_properties as $k => $v) {
            if ($v instanceof self) {
                $v->performValidation();
            }
        }
        $this->performValidation();
        return count($this->getErrors()) === 0;
    }

    /**
     * @return string[] list of validation errors according to OpenAPI spec.
     * @see validate()
     */
    public function getErrors(): array
    {
        $errors = [$this->_errors];
        foreach($this->_properties as $k => $v) {
            if ($v instanceof self) {
                $errors[] = $v->getErrors();
            }
        }
        return array_merge(...$errors);
    }

    /**
     * @param string $error error message to add.
     */
    protected function addError(string $error)
    {
        $this->_errors[] = $error;
    }

    protected function hasProperty(string $name): bool
    {
        return isset($this->_properties[$name]) || isset(static::attributes()[$name]);
    }

    protected function requireProperties(array $names)
    {
        foreach ($names as $name) {
            if (!isset($this->_properties[$name])) {
                $this->addError("Missing required property: $name");
            }
        }
    }

    protected function validateEmail(string $property)
    {
        if (!empty($this->$property) && strpos($this->$property, '@') === false) {
            $this->addError(__CLASS__ . '::$'.$property.' does not seem to be a valid email address: ' . $this->$property);
        }
    }

    protected function validateUrl(string $property)
    {
        if (!empty($this->$property) && strpos($this->$property, '//') === false) {
            $this->addError(__CLASS__ . '::$'.$property.' does not seem to be a valid URL: ' . $this->$property);
        }
    }

    public function __get($name)
    {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        }
        if (isset(static::attributes()[$name])) {
            return is_array(static::attributes()[$name]) ? [] : null;
        }
        throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
    }

    public function __set($name, $value)
    {
        throw new ReadonlyPropertyException('Setting read-only property: ' . get_class($this) . '::' . $name);
    }

    public function __isset($name)
    {
        if (isset($this->_properties[$name]) || isset(static::attributes()[$name])) {
            return $this->__get($name) === null;
        }

        return false;
    }

    public function __unset($name)
    {
        throw new ReadonlyPropertyException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
    }
}
