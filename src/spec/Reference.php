<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\DocumentContextInterface;
use cebe\openapi\exceptions\IOException;
use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\json\InvalidJsonPointerSyntaxException;
use cebe\openapi\json\JsonPointer;
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
class Reference implements SpecObjectInterface, DocumentContextInterface
{
    /**
     * @var string
     */
    private $_to;
    /**
     * @var string
     */
    private $_ref;
    /**
     * @var JsonReference|null
     */
    private $_jsonReference;
    /**
     * @var ReferenceContext
     */
    private $_context;
    /**
     * @var SpecObjectInterface|null
     */
    private $_baseDocument;
    /**
     * @var JsonPointer|null
     */
    private $_jsonPointer;
    /**
     * @var array
     */
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
            $this->_jsonReference = JsonReference::createFromReference($this->_ref);
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
        if (($pos = $this->getDocumentPosition()) !== null) {
            return array_map(function ($e) use ($pos) {
                return "[{$pos}] $e";
            }, $this->_errors);
        } else {
            return $this->_errors;
        }
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
     * @return SpecObjectInterface|array|null the resolved spec type.
     * You might want to call resolveReferences() on the resolved object to recursively resolve recursive references.
     * This is not done automatically to avoid recursion to run into the same function again.
     * If you call resolveReferences() make sure to replace the Reference with the resolved object first.
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
        if ($jsonReference === null) {
            if ($context->throwException) {
                throw new UnresolvableReferenceException(implode("\n", $this->getErrors()));
            }
            return $this;
        }
        try {
            if ($jsonReference->getDocumentUri() === '') {
                if ($context->mode === ReferenceContext::RESOLVE_MODE_INLINE) {
                    return $this;
                }

                // resolve in current document
                $baseSpec = $context->getBaseSpec();
                if ($baseSpec !== null) {
                    // TODO type error if resolved object does not match $this->_to ?
                    /** @var SpecObjectInterface $referencedObject */
                    $referencedObject = $jsonReference->getJsonPointer()->evaluate($baseSpec);
                    // transitive reference
                    if ($referencedObject instanceof Reference) {
                        $referencedObject = $this->resolveTransitiveReference($referencedObject, $context);
                    }
                    if ($referencedObject instanceof SpecObjectInterface) {
                        $referencedObject->setReferenceContext($context);
                    }
                    return $referencedObject;
                } else {
                    // if current document was loaded via reference, it may be null,
                    // so we load current document by URI instead.
                    $jsonReference = JsonReference::createFromUri($context->getUri(), $jsonReference->getJsonPointer());
                }
            }

            // resolve in external document
            $file = $context->resolveRelativeUri($jsonReference->getDocumentUri());
            try {
                $referencedDocument = $context->fetchReferencedFile($file);
            } catch (\Throwable $e) {
                $exception = new UnresolvableReferenceException(
                    "Failed to resolve Reference '$this->_ref' to $this->_to Object: " . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
                $exception->context = $this->getDocumentPosition();
                throw $exception;
            }

            $referencedDocument = $this->adjustRelativeReferences($referencedDocument, $file, null, $context);
            $referencedObject = $context->resolveReferenceData($file, $jsonReference->getJsonPointer(), $referencedDocument, $this->_to);

            if ($referencedObject instanceof DocumentContextInterface) {
                if ($referencedObject->getDocumentPosition() === null && $this->getDocumentPosition() !== null) {
                    $referencedObject->setDocumentContext($context->getBaseSpec(), $this->getDocumentPosition());
                }
            }

            // transitive reference
            if ($referencedObject instanceof Reference) {
                if ($context->mode === ReferenceContext::RESOLVE_MODE_INLINE && strncmp($referencedObject->getReference(), '#', 1) === 0) {
                    $referencedObject->setContext($context);
                } else {
                    return $this->resolveTransitiveReference($referencedObject, $context);
                }
            } else {
                if ($referencedObject instanceof SpecObjectInterface) {
                    $referencedObject->setReferenceContext($context);
                }
            }

            return $referencedObject;
        } catch (NonexistentJsonPointerReferenceException $e) {
            $message = "Failed to resolve Reference '$this->_ref' to $this->_to Object: " . $e->getMessage();
            if ($context->throwException) {
                $exception = new UnresolvableReferenceException($message, 0, $e);
                $exception->context = $this->getDocumentPosition();
                throw $exception;
            }
            $this->_errors[] = $message;
            $this->_jsonReference = null;
            return $this;
        } catch (UnresolvableReferenceException $e) {
            $e->context = $this->getDocumentPosition();
            if ($context->throwException) {
                throw $e;
            }
            $this->_errors[] = $e->getMessage();
            $this->_jsonReference = null;
            return $this;
        }
    }

    private function resolveTransitiveReference(Reference $referencedObject, ReferenceContext $context)
    {
        if ($referencedObject->_to === null) {
            $referencedObject->_to = $this->_to;
        }
        $referencedObject->setContext($context);

        if ($referencedObject === $this) { // catch recursion
            throw new UnresolvableReferenceException('Cyclic reference detected on a Reference Object.');
        }

        $transitiveRefResult = $referencedObject->resolve();

        if ($transitiveRefResult === $this) { // catch recursion
            throw new UnresolvableReferenceException('Cyclic reference detected on a Reference Object.');
        }
        return $transitiveRefResult;
    }

    /**
     * Adjust relative references inside of the file to match the context of the base file
     */
    private function adjustRelativeReferences($referencedDocument, $basePath, $baseDocument = null, $oContext = null)
    {
        $context = new ReferenceContext(null, $basePath);
        if ($baseDocument === null) {
            $baseDocument = $referencedDocument;
        }

        foreach ($referencedDocument as $key => $value) {
            // adjust reference URLs
            if ($key === '$ref' && is_string($value)) {
                if (isset($value[0]) && $value[0] === '#') {
                    // direcly inline references in the same document,
                    // these are not going to be valid in the new context anymore
                    $inlineDocument = (new JsonPointer(substr($value, 1)))->evaluate($baseDocument);
                    return $this->adjustRelativeReferences($inlineDocument, $basePath, $baseDocument, $oContext);
                }
                $referencedDocument[$key] = $context->resolveRelativeUri($value);
                $parts = explode('#', $referencedDocument[$key], 2);
                if ($parts[0] === $oContext->getUri()) {
                    $referencedDocument[$key] = '#' . ($parts[1] ?? '');
                } else {
                    $referencedDocument[$key] = $this->makeRelativePath($oContext->getUri(), $referencedDocument[$key]);
                }
                continue;
            }
            // adjust URLs for 'externalValue' references in Example Objects
            // https://spec.openapis.org/oas/v3.0.3#example-object
            if ($key === 'externalValue' && is_string($value)) {
                $referencedDocument[$key] = $this->makeRelativePath($oContext->getUri(), $context->resolveRelativeUri($value));
                continue;
            }
            if (is_array($value)) {
                $referencedDocument[$key] = $this->adjustRelativeReferences($value, $basePath, $baseDocument, $oContext);
            }
        }
        return $referencedDocument;
    }

    /**
     * If $path can be expressed relative to $base, make it a relative path, otherwise $path is returned.
     * @param string $base
     * @param string $path
     */
    private function makeRelativePath($base, $path)
    {
        if (strpos($path, dirname($base)) === 0) {
            return './' . substr($path, strlen(dirname($base) . '/'));
        }

        return $path;
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
