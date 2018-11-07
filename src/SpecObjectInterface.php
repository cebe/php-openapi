<?php
/**
 * Created by PhpStorm.
 * User: cebe
 * Date: 07.11.18
 * Time: 23:22
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

}