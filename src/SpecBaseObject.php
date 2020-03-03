<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnknownPropertyException;
use cebe\openapi\json\JsonPointer;
use cebe\openapi\json\JsonReference;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Type;

/**
 * Base class for all spec objects.
 *
 * Implements property management and validation basics.
 *
 */
abstract class SpecBaseObject implements SpecObjectInterface, DocumentContextInterface
{
    private $_properties = [];
    private $_errors = [];

    private $_recursingSerializableData = false;
    private $_recursingValidate = false;
    private $_recursingErrors = false;
    private $_recursingReferences = false;
    private $_recursingReferenceContext = false;
    private $_recursingDocumentContext = false;

    private $_baseDocument;
    private $_jsonPointer;


    /**
     * @return array array of attributes available in this object.
     */
    abstract protected function attributes(): array;

    /**
     * @return array array of attributes default values.
     */
    protected function attributeDefaults(): array
    {
        return [];
    }

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

            if ($type === Type::BOOLEAN) {
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
                        if (isset($data[$property]['$ref'])) {
                            $this->_properties[$property] = new Reference($data[$property], null);
                        } else {
                            // array
                            $this->_properties[$property] = [];
                            foreach ($data[$property] as $item) {
                                if ($type[0] === Type::STRING) {
                                    if (!is_string($item)) {
                                        $this->_errors[] = "property '$property' must be array of strings, but array has " . gettype($item) . " element.";
                                    }
                                    $this->_properties[$property][] = $item;
                                } elseif (Type::isScalar($type[0])) {
                                    $this->_properties[$property][] = $item;
                                } elseif ($type[0] === Type::ANY) {
                                    if (is_array($item) && isset($item['$ref'])) {
                                        $this->_properties[$property][] = new Reference($item, null);
                                    } else {
                                        $this->_properties[$property][] = $item;
                                    }
                                } else {
                                    $this->_properties[$property][] = $this->instantiate($type[0], $item);
                                }
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
                            if ($type[1] === Type::STRING) {
                                if (!is_string($item)) {
                                    $this->_errors[] = "property '$property' must be map<string, string>, but entry '$key' is of type " . \gettype($item) . '.';
                                }
                                $this->_properties[$property][$key] = $item;
                            } elseif ($type[1] === Type::ANY || Type::isScalar($type[1])) {
                                $this->_properties[$property][$key] = $item;
                            } else {
                                $this->_properties[$property][$key] = $this->instantiate($type[1], $item);
                            }
                        }
                        break;
                }
            } elseif (Type::isScalar($type)) {
                $this->_properties[$property] = $data[$property];
            } elseif ($type === Type::ANY) {
                if (is_array($data[$property]) && isset($data[$property]['$ref'])) {
                    $this->_properties[$property] = new Reference($data[$property], null);
                } else {
                    $this->_properties[$property] = $data[$property];
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
    protected function instantiate($type, $data)
    {
        if ($data instanceof $type || $data instanceof Reference) {
            return $data;
        }

        if (is_array($data) && isset($data['$ref'])) {
            return new Reference($data, $type);
        }

        if (!is_array($data)) {
            throw new TypeErrorException(
                "Unable to instantiate {$type} Object with data '" . print_r($data, true) . "' at " . $this->getDocumentPosition()
            );
        }
        try {
            return new $type($data);
        } catch (\TypeError $e) {
            throw new TypeErrorException(
                "Unable to instantiate {$type} Object with data '" . print_r($data, true) . "' at " . $this->getDocumentPosition(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @return mixed returns the serializable data of this object for converting it
     * to JSON or YAML.
     */
    public function getSerializableData()
    {
        if ($this->_recursingSerializableData) {
            // return a reference
            return (object) ['$ref' => JsonReference::createFromUri('', $this->getDocumentPosition())->getReference()];
        }
        $this->_recursingSerializableData = true;

        $data = $this->_properties;
        foreach ($data as $k => $v) {
            if ($v instanceof SpecObjectInterface) {
                $data[$k] = $v->getSerializableData();
            } elseif (is_array($v)) {
                $toObject = false;
                $j = 0;
                foreach ($v as $i => $d) {
                    if ($j++ !== $i) {
                        $toObject = true;
                    }
                    if ($d instanceof SpecObjectInterface) {
                        $data[$k][$i] = $d->getSerializableData();
                    }
                }
                if ($toObject) {
                    $data[$k] = (object) $data[$k];
                }
            }
        }

        $this->_recursingSerializableData = false;

        return (object) $data;
    }

    /**
     * Validate object data according to OpenAPI spec.
     * @return bool whether the loaded data is valid according to OpenAPI spec
     * @see getErrors()
     */
    public function validate(): bool
    {
        // avoid recursion to get stuck in a loop
        if ($this->_recursingValidate) {
            return true;
        }
        $this->_recursingValidate = true;
        $valid = true;
        foreach ($this->_properties as $v) {
            if ($v instanceof SpecObjectInterface) {
                if (!$v->validate()) {
                    $valid = false;
                }
            } elseif (is_array($v)) {
                foreach ($v as $item) {
                    if ($item instanceof SpecObjectInterface) {
                        if (!$item->validate()) {
                            $valid = false;
                        }
                    }
                }
            }
        }
        $this->_recursingValidate = false;

        $this->performValidation();

        if (!empty($this->_errors)) {
            $valid = false;
        }

        return $valid;
    }

    /**
     * @return string[] list of validation errors according to OpenAPI spec.
     * @see validate()
     */
    public function getErrors(): array
    {
        // avoid recursion to get stuck in a loop
        if ($this->_recursingErrors) {
            return [];
        }
        $this->_recursingErrors = true;

        if (($pos = $this->getDocumentPosition()) !== null) {
            $errors = [
                array_map(function ($e) use ($pos) {
                    return "[{$pos->getPointer()}] $e";
                }, $this->_errors)
            ];
        } else {
            $errors = [$this->_errors];
        }
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

        $this->_recursingErrors = false;

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
        return isset($this->_properties[$name]) || isset($this->attributes()[$name]);
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
        $defaults = $this->attributeDefaults();
        if (array_key_exists($name, $defaults)) {
            return $defaults[$name];
        }
        if (isset($this->attributes()[$name])) {
            if (is_array($this->attributes()[$name])) {
                return [];
            } elseif ($this->attributes()[$name] === Type::BOOLEAN) {
                return false;
            }
            return null;
        }
        throw new UnknownPropertyException('Getting unknown property: ' . \get_class($this) . '::' . $name);
    }

    public function __set($name, $value)
    {
        $this->_properties[$name] = $value;
    }

    public function __isset($name)
    {
        if (isset($this->_properties[$name]) || isset($this->attributeDefaults()[$name]) || isset($this->attributes()[$name])) {
            return $this->__get($name) !== null;
        }

        return false;
    }

    public function __unset($name)
    {
        unset($this->_properties[$name]);
    }

    /**
     * Resolves all Reference Objects in this object and replaces them with their resolution.
     * @throws exceptions\UnresolvableReferenceException in case resolving a reference fails.
     */
    public function resolveReferences(ReferenceContext $context = null)
    {
        // avoid recursion to get stuck in a loop
        if ($this->_recursingReferences) {
            return;
        }
        $this->_recursingReferences = true;

        foreach ($this->_properties as $property => $value) {
            if ($value instanceof Reference) {
                $referencedObject = $value->resolve($context);
                $this->_properties[$property] = $referencedObject;
                if (!$referencedObject instanceof Reference && $referencedObject instanceof SpecObjectInterface) {
                    $referencedObject->resolveReferences();
                }
            } elseif ($value instanceof SpecObjectInterface) {
                $value->resolveReferences($context);
            } elseif (is_array($value)) {
                foreach ($value as $k => $item) {
                    if ($item instanceof Reference) {
                        $referencedObject = $item->resolve($context);
                        $this->_properties[$property][$k] = $referencedObject;
                        if (!$referencedObject instanceof Reference && $referencedObject instanceof SpecObjectInterface) {
                            $referencedObject->resolveReferences();
                        }
                    } elseif ($item instanceof SpecObjectInterface) {
                        $item->resolveReferences($context);
                    }
                }
            }
        }

        $this->_recursingReferences = false;
    }

    /**
     * Set context for all Reference Objects in this object.
     */
    public function setReferenceContext(ReferenceContext $context)
    {
        // avoid recursion to get stuck in a loop
        if ($this->_recursingReferenceContext) {
            return;
        }
        $this->_recursingReferenceContext = true;

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

        $this->_recursingReferenceContext = false;
    }

    /**
     * Provide context information to the object.
     *
     * Context information contains a reference to the base object where it is contained in
     * as well as a JSON pointer to its position.
     * @param SpecObjectInterface $baseDocument
     * @param JsonPointer $jsonPointer
     */
    public function setDocumentContext(SpecObjectInterface $baseDocument, JsonPointer $jsonPointer)
    {
        $this->_baseDocument = $baseDocument;
        $this->_jsonPointer = $jsonPointer;

        // avoid recursion to get stuck in a loop
        if ($this->_recursingDocumentContext) {
            return;
        }
        $this->_recursingDocumentContext = true;

        foreach ($this->_properties as $property => $value) {
            if ($value instanceof DocumentContextInterface) {
                $value->setDocumentContext($baseDocument, $jsonPointer->append($property));
            } elseif (is_array($value)) {
                foreach ($value as $k => $item) {
                    if ($item instanceof DocumentContextInterface) {
                        $item->setDocumentContext($baseDocument, $jsonPointer->append($property)->append($k));
                    }
                }
            }
        }

        $this->_recursingDocumentContext = false;
    }

    /**
     * @return SpecObjectInterface|null returns the base document where this object is located in.
     * Returns `null` if no context information was provided by [[setDocumentContext]].
     */
    public function getBaseDocument(): ?SpecObjectInterface
    {
        return $this->_baseDocument;
    }

    /**
     * @return JsonPointer|null returns a JSON pointer describing the position of this object in the base document.
     * Returns `null` if no context information was provided by [[setDocumentContext]].
     */
    public function getDocumentPosition(): ?JsonPointer
    {
        return $this->_jsonPointer;
    }
}
