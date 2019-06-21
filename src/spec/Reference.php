<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\json\InvalidJsonPointerSyntaxException;
use cebe\openapi\json\JsonReference;
use cebe\openapi\json\NonexistentJsonPointerReferenceException;
use cebe\openapi\ReferenceContext;
use cebe\openapi\SpecObjectInterface;
use Symfony\Component\Yaml\Yaml;

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
    private $_jsonReference;
    private $_context;

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
                "Unable to instantiate Reference Object with data '" . print_r($data, true) . "'."
            );
        }
        if ($to !== null && !is_subclass_of($to, SpecObjectInterface::class, true)) {
            throw new TypeErrorException(
                "Unable to instantiate Reference Object, Referenced Class type must implement SpecObjectInterface."
            );
        }
        if (!is_string($data['$ref'])) {
            throw new TypeErrorException(
                'Unable to instantiate Reference Object, value of $ref must be a string.'
            );
        }
        $this->_to = $to;
        $this->_ref = $data['$ref'];
        try {
            $this->_jsonReference = JsonReference::createFromReference($data['$ref']);
        } catch (InvalidJsonPointerSyntaxException $e) {
            $this->_errors[] = 'Reference: value of $ref is not a valid JSON pointer: ' . $e->getMessage();
        }
        if (count($data) !== 1) {
            $this->_errors[] = 'Reference: additional properties are given. Only $ref should be set in a Reference Object.';
        }
    }

    /**
     * @return mixed returns the serializable data of this object for converting it
     * to JSON or YAML.
     */
    public function getSerializableData()
    {
        return (object) ['$ref' => $this->_ref];
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
     * @return JsonReference the JSON Reference.
     */
    public function getJsonReference(): JsonReference
    {
        return $this->_jsonReference;
    }

    /**
     * @param ReferenceContext $context
     */
    public function setContext(ReferenceContext $context)
    {
        $this->_context = $context;
    }

    /**
     * @return ReferenceContext
     */
    public function getContext() : ?ReferenceContext
    {
        return $this->_context;
    }

    /**
     * Resolve this reference.
     * @param ReferenceContext $context the reference context to use for resolution.
     * If not specified, `getContext()` will be called to determine the context, if
     * that does not return a context, the UnresolvableReferenceException will be thrown.
     * @return SpecObjectInterface the resolved spec type.
     * @throws UnresolvableReferenceException in case of errors.
     */
    public function resolve(ReferenceContext $context = null)
    {
        if ($context === null) {
            $context = $this->getContext();
            if ($context === null) {
                throw new UnresolvableReferenceException('No context given for resolving reference.');
            }
        }
        $jsonReference = $this->_jsonReference;
        try {
            if ($jsonReference->getDocumentUri() === '') {
                // TODO type error if resolved object does not match $this->_to ?
                return $jsonReference->getJsonPointer()->evaluate($context->getBaseSpec());
            }
            $file = $context->resolveRelativeUri($jsonReference->getDocumentUri());
            // TODO could be a good idea to cache loaded files in current context to avoid loading the same files over and over again
            $referencedDocument = $this->fetchReferencedFile($file);
            $referencedData = $jsonReference->getJsonPointer()->evaluate($referencedDocument);

            /** @var $referencedObject SpecObjectInterface */
            $referencedObject = new $this->_to($referencedData);
            if ($jsonReference->getJsonPointer()->getPointer() === '') {
                $referencedObject->setReferenceContext(new ReferenceContext($referencedObject, $file));
            } else {
                // TODO resolving references recursively does not work as we do not know the base type of the file at this point
//                $referencedObject->resolveReferences(new ReferenceContext($referencedObject, $file));
            }

            return $referencedObject;
        } catch (NonexistentJsonPointerReferenceException $e) {
            throw new UnresolvableReferenceException("Failed to resolve Reference '$this->_ref' to $this->_to Object: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws UnresolvableReferenceException
     */
    private function fetchReferencedFile($uri)
    {
        try {
            $content = file_get_contents($uri);
            // TODO lazy content detection, should probably be improved
            if (strpos(ltrim($content), '{') === 0) {
                return json_decode($content, true);
            } else {
                return Yaml::parse($content);
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
    public function resolveReferences(ReferenceContext $context = null)
    {
        throw new UnresolvableReferenceException('Cyclic reference detected, resolveReferences() called on a Reference Object.');
    }

    /**
     * Set context for all Reference Objects in this object.
     * @throws UnresolvableReferenceException
     */
    public function setReferenceContext(ReferenceContext $context)
    {
        throw new UnresolvableReferenceException('Cyclic reference detected, setReferenceContext() called on a Reference Object.');
    }
}
