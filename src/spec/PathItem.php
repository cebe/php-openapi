<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\ReferenceContext;
use cebe\openapi\SpecBaseObject;
use cebe\openapi\SpecObjectInterface;
use cebe\openapi\json\JsonPointer;

/**
 * Describes the operations available on a single path.
 *
 * A Path Item MAY be empty, due to ACL constraints. The path itself is still exposed to the documentation
 * viewer but they will not know which operations and parameters are available.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#pathItemObject
 *
 * @property string $summary
 * @property string $description
 * @property Operation|null $get
 * @property Operation|null $put
 * @property Operation|null $post
 * @property Operation|null $delete
 * @property Operation|null $options
 * @property Operation|null $head
 * @property Operation|null $patch
 * @property Operation|null $trace
 * @property Server[] $servers
 * @property Parameter[]|Reference[] $parameters
 *
 */
class PathItem extends SpecBaseObject
{
    /**
     * @var Reference|null
     */
    private $_ref;


    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'summary' => Type::STRING,
            'description' => Type::STRING,
            'get' => Operation::class,
            'put' => Operation::class,
            'post' => Operation::class,
            'delete' => Operation::class,
            'options' => Operation::class,
            'head' => Operation::class,
            'patch' => Operation::class,
            'trace' => Operation::class,
            'servers' => [Server::class],
            'parameters' => [Parameter::class],
        ];
    }

    /**
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     * @throws \cebe\openapi\exceptions\TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data)
    {
        if (isset($data['$ref'])) {
            // Allows for an external definition of this path item.
            // $ref in a Path Item Object is not a Reference.
            // https://github.com/OAI/OpenAPI-Specification/issues/1038
            $this->_ref = new Reference(['$ref' => $data['$ref']], PathItem::class);
            unset($data['$ref']);
        }

        parent::__construct($data);
    }

    /**
     * @return mixed returns the serializable data of this object for converting it
     * to JSON or YAML.
     */
    public function getSerializableData()
    {
        $data = parent::getSerializableData();
        if ($this->_ref instanceof Reference) {
            $data->{'$ref'} = $this->_ref->getReference();
        }
        if (isset($data->servers) && empty($data->servers)) {
            unset($data->servers);
        }
        if (isset($data->parameters) && empty($data->parameters)) {
            unset($data->parameters);
        }
        return $data;
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
        // no required arguments
    }

    /**
     * Return all operations of this Path.
     * @return Operation[]
     */
    public function getOperations()
    {
        $operations = [];
        foreach ($this->attributes() as $attribute => $type) {
            if ($type === Operation::class && isset($this->$attribute)) {
                $operations[$attribute] = $this->$attribute;
            }
        }
        return $operations;
    }

    /**
     * Allows for an external definition of this path item. The referenced structure MUST be in the format of a
     * PathItem Object. The properties of the referenced structure are merged with the local Path Item Object.
     * If the same property exists in both, the referenced structure and the local one, this is a conflict.
     * In this case the behavior is *undefined*.
     * @return Reference|null
     */
    public function getReference(): ?Reference
    {
        return $this->_ref;
    }

    /**
     * Set context for all Reference Objects in this object.
     */
    public function setReferenceContext(ReferenceContext $context)
    {
        if ($this->_ref instanceof Reference) {
            $this->_ref->setContext($context);
        }
        parent::setReferenceContext($context);
    }

    /**
     * Resolves all Reference Objects in this object and replaces them with their resolution.
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException in case resolving a reference fails.
     */
    public function resolveReferences(ReferenceContext $context = null)
    {
        if ($this->_ref instanceof Reference) {
            $pathItem = $this->_ref->resolve($context);
            $this->_ref = null;
            // The properties of the referenced structure are merged with the local Path Item Object.
            foreach (self::attributes() as $attribute => $type) {
                if (!isset($pathItem->$attribute)) {
                    continue;
                }
                // If the same property exists in both, the referenced structure and the local one, this is a conflict.
                if (isset($this->$attribute) && !empty($this->$attribute)) {
                    $this->addError("Conflicting properties, property '$attribute' exists in local PathItem and also in the referenced one.");
                }
                $this->$attribute = $pathItem->$attribute;

                // resolve references in all properties assinged from the reference
                // use the referenced object context in this case
                if ($this->$attribute instanceof Reference) {
                    $referencedObject = $this->$attribute->resolve();
                    $this->$attribute = $referencedObject;
                    if (!$referencedObject instanceof Reference && $referencedObject !== null) {
                        $referencedObject->resolveReferences();
                    }
                } elseif ($this->$attribute instanceof SpecObjectInterface) {
                    $this->$attribute->resolveReferences();
                } elseif (is_array($this->$attribute)) {
                    foreach ($this->$attribute as $k => $item) {
                        if ($item instanceof Reference) {
                            $referencedObject = $item->resolve();
                            $this->$attribute = [$k => $referencedObject] + $this->$attribute;
                            if (!$referencedObject instanceof Reference && $referencedObject !== null) {
                                $referencedObject->resolveReferences();
                            }
                        } elseif ($item instanceof SpecObjectInterface) {
                            $item->resolveReferences();
                        }
                    }
                }
            }
        }
        parent::resolveReferences($context);
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
        parent::setDocumentContext($baseDocument, $jsonPointer);
        if ($this->_ref instanceof Reference) {
            $this->_ref->setDocumentContext($baseDocument, $jsonPointer->append('$ref'));
        }
    }
}
