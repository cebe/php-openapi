<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use ArrayAccess;
use ArrayIterator;
use cebe\openapi\DocumentContextInterface;
use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\json\JsonPointer;
use cebe\openapi\ReferenceContext;
use cebe\openapi\SpecObjectInterface;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * A container for the expected responses of an operation.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#responsesObject
 */
class Responses implements SpecObjectInterface, DocumentContextInterface, ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @var (Response|Reference|null)[]
     */
    private $_responses = [];
    private $_errors = [];

    private $_baseDocument;
    private $_jsonPointer;


    /**
     * Create an object from spec data.
     * @param Response[]|Reference[]|array[] $data spec data read from YAML or JSON
     * @throws TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data)
    {
        foreach ($data as $statusCode => $response) {
            // From Spec: This field MUST be enclosed in quotation marks (for example, "200") for compatibility between JSON and YAML.
            $statusCode = (string) $statusCode;
            if (preg_match('~^(?:default|[1-5](?:[0-9][0-9]|XX))$~', $statusCode)) {
                if ($response instanceof Response || $response instanceof Reference) {
                    $this->_responses[$statusCode] = $response;
                } elseif (is_array($response) && isset($response['$ref'])) {
                    $this->_responses[$statusCode] = new Reference($response, Response::class);
                } elseif (is_array($response)) {
                    $this->_responses[$statusCode] = new Response($response);
                } else {
                    $givenType = gettype($response);
                    if ($givenType === 'object') {
                        $givenType = get_class($response);
                    }
                    throw new TypeErrorException(sprintf('Response MUST be either an array, a Response or a Reference object, "%s" given', $givenType));
                }
            } else {
                $this->_errors[] = "Responses: $statusCode is not a valid HTTP status code.";
            }
        }
    }

    /**
     * @return mixed returns the serializable data of this object for converting it
     * to JSON or YAML.
     */
    public function getSerializableData()
    {
        $data = [];
        foreach ($this->_responses as $statusCode => $response) {
            $data[$statusCode] = ($response === null) ? null : $response->getSerializableData();
        }
        return (object) $data;
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
     * @return Response|Reference|null
     */
    public function getResponse($statusCode)
    {
        return $this->_responses[$statusCode] ?? null;
    }

    /**
     * @param string $statusCode HTTP status code
     * @param Response|Reference $response
     */
    public function addResponse($statusCode, $response): void
    {
        $this->_responses[$statusCode] = $response;
    }

    /**
     * @param string $statusCode HTTP status code
     */
    public function removeResponse($statusCode)
    {
        unset($this->_responses[$statusCode]);
    }

    /**
     * @return (Response|Reference|null)[]
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
        if (($pos = $this->getDocumentPosition()) !== null) {
            $errors = [
                array_map(function ($e) use ($pos) {
                    return "[{$pos}] $e";
                }, $this->_errors)
            ];
        } else {
            $errors = [$this->_errors];
        }

        foreach ($this->_responses as $response) {
            if ($response === null) {
                continue;
            }
            $errors[] = $response->getErrors();
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
        return $this->hasResponse($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getResponse($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     */
    public function offsetSet($offset, $value)
    {
        $this->addResponse($offset, $value);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     */
    public function offsetUnset($offset)
    {
        $this->removeResponse($offset);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * The return value is cast to an integer.
     */
    public function count()
    {
        return count($this->_responses);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_responses);
    }

    /**
     * Resolves all Reference Objects in this object and replaces them with their resolution.
     * @throws UnresolvableReferenceException
     */
    public function resolveReferences(ReferenceContext $context = null)
    {
        foreach ($this->_responses as $key => $response) {
            if ($response instanceof Reference) {
                /** @var Response|Reference|null $referencedObject */
                $referencedObject = $response->resolve($context);
                $this->_responses[$key] = $referencedObject;
                if (!$referencedObject instanceof Reference && $referencedObject !== null) {
                    $referencedObject->resolveReferences();
                }
            } else {
                $response->resolveReferences($context);
            }
        }
    }

    /**
     * Set context for all Reference Objects in this object.
     */
    public function setReferenceContext(ReferenceContext $context)
    {
        foreach ($this->_responses as $key => $response) {
            if ($response instanceof Reference) {
                $response->setContext($context);
            } else {
                $response->setReferenceContext($context);
            }
        }
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

        foreach ($this->_responses as $key => $response) {
            if ($response instanceof DocumentContextInterface) {
                $response->setDocumentContext($baseDocument, $jsonPointer->append($key));
            }
        }
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
