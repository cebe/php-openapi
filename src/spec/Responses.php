<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecObjectInterface;

/**
 * A container for the expected responses of an operation.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#responsesObject
 */
class Responses implements SpecObjectInterface
{
    private $_responses = [];
    private $_errors = [];


    /**
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     */
    public function __construct(array $data)
    {
        foreach ($data as $statusCode => $response) {
            if ((is_numeric($statusCode) && $statusCode >= 100 && $statusCode <= 600) || $statusCode === 'default') {
                $this->_responses[$statusCode] = $response;
            } else {
                $this->_errors[] = "$statusCode is not a valid HTTP status code.";
            }
        }
    }

    /**
     * @param string $statusCode HTTP status code
     * @return bool
     */
    public function hasResponse($statusCode): bool
    {
        return isset($this->_responses[$statusCode]);
    }

    /**
     * @param string $statusCode HTTP status code
     * @return PathItem
     */
    public function getResponse($statusCode): ?Response
    {
        return $this->_responses[$statusCode] ?? null;
    }

    /**
     * @return Response[]
     */
    public function getResponses(): array
    {
        return $this->_responses;
    }

    /**
     * Validate object data according to OpenAPI spec.
     * @return bool whether the loaded data is valid according to OpenAPI spec
     * @see getErrors()
     */
    public function validate(): bool
    {
        $valid = true;
        foreach ($this->_responses as $key => $response) {
            if ($response === null) {
                continue;
            }
            if (!$response->validate()) {
                $valid = false;
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
        foreach ($this->_responses as $response) {
            if ($response === null) {
                continue;
            }
            $errors[] = $response->getErrors();
        }
        return array_merge(...$errors);
    }
}
