<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

/**
 * This interface is implemented by all classes that represent objects from the OpenAPI Spec.
 */
interface SpecObjectInterface
{
    /**
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     */
    public function __construct(array $data);

    /**
     * @return mixed returns the serializable data of this object for converting it
     * to JSON or YAML.
     */
    public function getSerializableData();

    /**
     * Validate object data according to OpenAPI spec.
     * @return bool whether the loaded data is valid according to OpenAPI spec
     * @see getErrors()
     */
    public function validate(): bool;

    /**
     * @return string[] list of validation errors according to OpenAPI spec.
     * @see validate()
     */
    public function getErrors(): array;

    /**
     * Resolves all Reference Objects in this object and replaces them with their resolution.
     */
    public function resolveReferences(ReferenceContext $context = null);

    /**
     * Set context for all Reference Objects in this object.
     */
    public function setReferenceContext(ReferenceContext $context);
}
