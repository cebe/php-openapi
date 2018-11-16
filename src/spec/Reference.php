<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\Reader;
use cebe\openapi\ReferenceContext;
use cebe\openapi\SpecBaseObject;
use cebe\openapi\SpecObjectInterface;

/**
 * Reference Object
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#referenceObject
 * @link https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03
 * @link https://tools.ietf.org/html/rfc6901
 *
 */
class Reference implements SpecObjectInterface
{
    private $_to;
    private $_ref;

    private $_errors = [];

    /**
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     * @param string $to class name of the type referenced by this Reference
     * @throws TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data, string $to = null)
    {
        if (!isset($data['$ref'])) {
            throw new TypeErrorException(
                "Unable to instantiate Reference Object with data '" . print_r($data, true) . "'"
            );
        }
        if ($to !== null && !is_subclass_of($to, SpecObjectInterface::class, true)) {
            throw new TypeErrorException(
                "Unable to instantiate Reference Object, Referenced Class type must implement SpecObjectInterface"
            );
        }
        $this->_to = $to;
        if (count($data) !== 1) {
            $this->_errors[] = 'Reference: additional properties are given. Only $ref should be set in a Reference Object.';
        }
        if (!is_string($data['$ref'])) {
            $this->_errors[] = 'Reference: value of $ref must be a string.';
        }
        if (!empty($this->_errors)) {
            return;
        }
        $this->_ref = $data['$ref'];
    }

    /**
     * Validate object data according to OpenAPI spec.
     * @return bool whether the loaded data is valid according to OpenAPI spec
     * @see getErrors()
     */
    public function validate(): bool
    {
        return empty($this->_errors);
    }

    /**
     * @return string[] list of validation errors according to OpenAPI spec.
     * @see validate()
     */
    public function getErrors(): array
    {
        return $this->_errors;
    }

    /**
     * @return string the reference string.
     */
    public function getReference()
    {
        return $this->_ref;
    }

    /**
     * @param ReferenceContext $context
     * @return SpecObjectInterface the resolved spec type.
     * @throws UnresolvableReferenceException in case of errors.
     */
    public function resolve(ReferenceContext $context)
    {
        $currentReference = $context->getBaseSpec();
        if (($pos = strpos($this->_ref, '#')) === 0) {
            // resolve in current document
            $jsonPointer = substr($this->_ref, 1);
        } else {
            $file =  ($pos === false) ? $this->_ref : substr($this->_ref, 0, $pos);
            $jsonPointer = substr($this->_ref, $pos + 1);
            $currentReference = $this->fetchReferencedFile($file, $context);
            // TODO could be a good idea to cache loaded files in current context to avoid loading the same files over and over again
            $currentReference->resolveReferences(new ReferenceContext($currentReference, $file));
        }

        // resolve JSON Pointer
        if ($jsonPointer === '') {
            // empty pointer references the whole document
            return $currentReference;
        }
        $pointerParts = explode('/', ltrim($jsonPointer, '/'));
        foreach ($pointerParts as $part) {
            $part = strtr($part, [
                '~1' => '/',
                '~0' => '~',
            ]);

            if (is_array($currentReference) || $currentReference instanceof \ArrayAccess) {
                if (!isset($currentReference[$part])) {
                    throw new UnresolvableReferenceException(
                        "Failed to resolve Reference '$this->_ref' to $this->_to Object: path $jsonPointer does not exist in referenced object."
                    );
                }
                $currentReference = $currentReference[$part];
            } elseif (is_object($currentReference)) {
                if (!isset($currentReference->$part)) {
                    throw new UnresolvableReferenceException(
                        "Failed to resolve Reference '$this->_ref' to $this->_to Object: path $jsonPointer does not exist in referenced object."
                    );
                }
                $currentReference = $currentReference->$part;
            } else {
                throw new UnresolvableReferenceException(
                    "Failed to resolve Reference '$this->_ref' to $this->_to Object: path $jsonPointer does not exist in referenced object."
                );
            }
        }

        return $currentReference;
    }

    /**
     * @throws UnresolvableReferenceException
     */
    private function fetchReferencedFile($uri, ReferenceContext $context): SpecObjectInterface
    {
        $uri = $context->resolveRelativeUri($uri);
        try {
            $content = file_get_contents($uri);
            // TODO lazy content detection, should probably be improved
            if (strpos(ltrim($content), '{') === 0) {
                return Reader::readFromJson($content, $this->_to);
            } else {
                return Reader::readFromYaml($content, $this->_to);
            }
        } catch (\Throwable $e) {
            throw new UnresolvableReferenceException(
                "Failed to resolve Reference '$this->_ref' to $this->_to Object: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Resolves all Reference Objects in this object and replaces them with their resolution.
     * @throws UnresolvableReferenceException
     */
    public function resolveReferences(ReferenceContext $context)
    {
        throw new UnresolvableReferenceException('Cyclic reference detected, resolveReferences() called on a Reference Object.');
    }
}
