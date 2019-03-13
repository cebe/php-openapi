<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use ArrayAccess;
use ArrayIterator;
use cebe\openapi\exceptions\ReadonlyPropertyException;
use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\ReferenceContext;
use cebe\openapi\SpecObjectInterface;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Holds the relative paths to the individual endpoints and their operations.
 *
 * The path is appended to the URL from the Server Object in order to construct the full URL.
 * The Paths MAY be empty, due to ACL constraints.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#pathsObject
 *
 */
class Paths implements SpecObjectInterface, ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var PathItem[]
     */
    private $_paths = [];

    private $_errors = [];


    /**
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     * @throws TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data)
    {
        foreach ($data as $path => $object) {
            if ($object === null) {
                $this->_paths[$path] = null;
            } else {
                $this->_paths[$path] = new PathItem($object);
            }
        }
    }

    /**
     * @param string $name path name
     * @return bool
     */
    public function hasPath(string $name): bool
    {
        return isset($this->_paths[$name]);
    }

    /**
     * @param string $name path name
     * @return PathItem
     */
    public function getPath(string $name): ?PathItem
    {
        return $this->_paths[$name] ?? null;
    }

    /**
     * @return PathItem[]
     */
    public function getPaths(): array
    {
        return $this->_paths;
    }

    /**
     * Validate object data according to OpenAPI spec.
     * @return bool whether the loaded data is valid according to OpenAPI spec
     * @see getErrors()
     */
    public function validate(): bool
    {
        $valid = true;
        $this->_errors = [];
        foreach ($this->_paths as $key => $path) {
            if ($path === null) {
                continue;
            }
            if (!$path->validate()) {
                $valid = false;
            }
            if (strpos($key, '/') !== 0) {
                $this->_errors[] = "Path must begin with /: $key";
            }
        }
        return $valid && empty($this->_errors);
    }

    /**
     * @return string[] list of validation errors according to OpenAPI spec.
     * @see validate()
     */
    public function getErrors(): array
    {
        $errors = [$this->_errors];
        foreach ($this->_paths as $path) {
            if ($path === null) {
                continue;
            }
            $errors[] = $path->getErrors();
        }
        return array_merge(...$errors);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     * @return boolean true on success or false on failure.
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->hasPath($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return PathItem Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getPath($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @throws ReadonlyPropertyException because spec objects are read-only.
     */
    public function offsetSet($offset, $value)
    {
        throw new ReadonlyPropertyException('Setting read-only property: ' . \get_class($this) . '::' . $offset);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     * @throws ReadonlyPropertyException because spec objects are read-only.
     */
    public function offsetUnset($offset)
    {
        throw new ReadonlyPropertyException('Unsetting read-only property: ' . \get_class($this) . '::' . $offset);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_paths);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_paths);
    }

    /**
     * Resolves all Reference Objects in this object and replaces them with their resolution.
     * @throws UnresolvableReferenceException
     */
    public function resolveReferences(ReferenceContext $context = null)
    {
        foreach ($this->_paths as $key => $path) {
            if ($path === null) {
                continue;
            }
            $path->resolveReferences($context);
        }
    }

    /**
     * Set context for all Reference Objects in this object.
     */
    public function setReferenceContext(ReferenceContext $context)
    {
        foreach ($this->_paths as $key => $path) {
            if ($path === null) {
                continue;
            }
            $path->setReferenceContext($context);
        }
    }
}
