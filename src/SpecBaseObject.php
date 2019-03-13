<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

use cebe\openapi\exceptions\ReadonlyPropertyException;
use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnknownPropertyException;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Type;

/**
 * Base class for all spec objects.
 *
 * Implements property management and validation basics.
 *
 */
abstract class SpecBaseObject implements SpecObjectInterface
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
     * @throws TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data)
    {
        foreach ($this->attributes() as $property => $type) {
            if (!isset($data[$property])) {
                continue;
            }

            if ($type === Type::STRING || $type === Type::ANY) {
                $this->_properties[$property] = $data[$property];
            } elseif ($type === Type::BOOLEAN) {
                if (!\is_bool($data[$property])) {
                    $this->_errors[] = "property '$property' must be boolean, but " . gettype($data[$property]) . " given.";
                    continue;
                }
                $this->_properties[$property] = (bool) $data[$property];
            } elseif (\is_array($type)) {
                if (!\is_array($data[$property])) {
                    $this->_errors[] = "property '$property' must be array, but " . gettype($data[$property]) . " given.";
                    continue;
                }
                switch (\count($type)) {
                    case 1:
                        // array
                        $this->_properties[$property] = [];
                        foreach ($data[$property] as $item) {
                            if ($type[0] === Type::STRING) {
                                if (!is_string($item)) {
                                    $this->_errors[] = "property '$property' must be array of strings, but array has " . gettype($item) . " element.";
                                }
                                $this->_properties[$property][] = $item;
                            } elseif ($type[0] === Type::ANY || $type[0] === Type::BOOLEAN || $type[0] === Type::INTEGER) { // TODO simplify handling of scalar types
                                $this->_properties[$property][] = $item;
                            } else {
                                $this->_properties[$property][] = $this->instantiate($type[0], $item);
                            }
                        }
                        break;
                    case 2:
                        // map
                        if ($type[0] !== Type::STRING) {
                            throw new TypeErrorException('Invalid map key type: ' . $type[0]);
                        }
                        $this->_properties[$property] = [];
                        foreach ($data[$property] as $key => $item) {
                            if ($type[1] === 'string') {
                                if (!is_string($item)) {
                                    $this->_errors[] = "property '$property' must be map<string, string>, but entry '$key' is of type " . \gettype($item) . '.';
                                }
                                $this->_properties[$property][$key] = $item;
                            } elseif ($type[1] === Type::ANY || $type[1] === Type::BOOLEAN || $type[1] === Type::INTEGER) { // TODO simplify handling of scalar types
                                $this->_properties[$property][$key] = $item;
                            } else {
                                $this->_properties[$property][$key] = $this->instantiate($type[1], $item);
                            }
                        }
                        break;
                }
            } else {
                $this->_properties[$property] = $this->instantiate($type, $data[$property]);
            }
            unset($data[$property]);
        }
        foreach ($data as $additionalProperty => $value) {
            $this->_properties[$additionalProperty] = $value;
        }
    }

    /**
     * @throws TypeErrorException
     */
    private function instantiate($type, $data)
    {
        if (isset($data['$ref'])) {
            return new Reference($data, $type);
        }
        try {
            return new $type($data);
        } catch (\TypeError $e) {
            throw new TypeErrorException(
                "Unable to instantiate {$type} Object with data '" . print_r($data, true) . "'",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate object data according to OpenAPI spec.
     * @return bool whether the loaded data is valid according to OpenAPI spec
     * @see getErrors()
     */
    public function validate(): bool
    {
        foreach ($this->_properties as $v) {
            if ($v instanceof SpecObjectInterface) {
                $v->validate();
            } elseif (is_array($v)) {
                foreach ($v as $item) {
                    if ($item instanceof SpecObjectInterface) {
                        $item->validate();
                    }
                }
            }
        }
        $this->performValidation();
        return \count($this->getErrors()) === 0;
    }

    /**
     * @return string[] list of validation errors according to OpenAPI spec.
     * @see validate()
     */
    public function getErrors(): array
    {
        $errors = [$this->_errors];
        foreach ($this->_properties as $v) {
            if ($v instanceof SpecObjectInterface) {
                $errors[] = $v->getErrors();
            } elseif (is_array($v)) {
                foreach ($v as $item) {
                    if ($item instanceof SpecObjectInterface) {
                        $errors[] = $item->getErrors();
                    }
                }
            }
        }
        return array_merge(...$errors);
    }

    /**
     * @param string $error error message to add.
     */
    protected function addError(string $error, $class = '')
    {
        $shortName = explode('\\', $class);
        $this->_errors[] = end($shortName).$error;
    }

    protected function hasProperty(string $name): bool
    {
        return isset($this->_properties[$name]) || isset(static::attributes()[$name]);
    }

    protected function requireProperties(array $names)
    {
        foreach ($names as $name) {
            if (!isset($this->_properties[$name])) {
                $this->addError(" is missing required property: $name", get_class($this));
            }
        }
    }

    protected function validateEmail(string $property)
    {
        if (!empty($this->$property) && strpos($this->$property, '@') === false) {
            $this->addError('::$'.$property.' does not seem to be a valid email address: ' . $this->$property, get_class($this));
        }
    }

    protected function validateUrl(string $property)
    {
        if (!empty($this->$property) && strpos($this->$property, '//') === false) {
            $this->addError('::$'.$property.' does not seem to be a valid URL: ' . $this->$property, get_class($this));
        }
    }

    public function __get($name)
    {
        if (isset($this->_properties[$name])) {
            return $this->_properties[$name];
        }
        if (isset(static::attributes()[$name])) {
            if (is_array(static::attributes()[$name])) {
                return [];
            } elseif (static::attributes()[$name] === Type::BOOLEAN) {
                return false;
            }
            return null;
        }
        throw new UnknownPropertyException('Getting unknown property: ' . \get_class($this) . '::' . $name);
    }

    public function __set($name, $value)
    {
        throw new ReadonlyPropertyException('Setting read-only property: ' . \get_class($this) . '::' . $name);
    }

    public function __isset($name)
    {
        if (isset($this->_properties[$name]) || isset(static::attributes()[$name])) {
            return $this->__get($name) !== null;
        }

        return false;
    }

    public function __unset($name)
    {
        throw new ReadonlyPropertyException('Unsetting read-only property: ' . \get_class($this) . '::' . $name);
    }

    /**
     * Resolves all Reference Objects in this object and replaces them with their resolution.
     * @throws exceptions\UnresolvableReferenceException in case resolving a reference fails.
     */
    public function resolveReferences(ReferenceContext $context = null)
    {
        foreach ($this->_properties as $property => $value) {
            if ($value instanceof Reference) {
                $this->_properties[$property] = $value->resolve($context);
            } elseif ($value instanceof SpecObjectInterface) {
                $value->resolveReferences($context);
            } elseif (is_array($value)) {
                foreach ($value as $k => $item) {
                    if ($item instanceof Reference) {
                        $this->_properties[$property][$k] = $item->resolve($context);
                    } elseif ($item instanceof SpecObjectInterface) {
                        $item->resolveReferences($context);
                    }
                }
            }
        }
    }

    /**
     * Set context for all Reference Objects in this object.
     */
    public function setReferenceContext(ReferenceContext $context)
    {
        foreach ($this->_properties as $property => $value) {
            if ($value instanceof Reference) {
                $value->setContext($context);
            } elseif ($value instanceof SpecObjectInterface) {
                $value->setReferenceContext($context);
            } elseif (is_array($value)) {
                foreach ($value as $k => $item) {
                    if ($item instanceof Reference) {
                        $item->setContext($context);
                    } elseif ($item instanceof SpecObjectInterface) {
                        $item->setReferenceContext($context);
                    }
                }
            }
        }
    }
}
