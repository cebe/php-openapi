<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecObjectInterface;

/**
 * Holds the relative paths to the individual endpoints and their operations.
 *
 * The path is appended to the URL from the Server Object in order to construct the full URL.
 * The Paths MAY be empty, due to ACL constraints.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#pathsObject
 *
 */
class Paths implements SpecObjectInterface
{
    /**
     * @var PathItem[]
     */
    private $_paths = [];

    private $_errors = [];

    public function __construct(array $data)
    {
        foreach($data as $path => $object) {
            // TODO support reference
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
        foreach($this->_paths as $key => $path) {
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
}
