<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

use cebe\openapi\json\JsonPointer;

/**
 * Interface implemented by OpenAPI objects that provide functionality for context in the document.
 *
 * Allows an object to reference the base OpenAPI document as well as its own position inside of
 * the document in form of a [JSON pointer](https://tools.ietf.org/html/rfc6901).
 */
interface DocumentContextInterface
{
    /**
     * Provide context information to the object.
     *
     * Context information contains a reference to the base object where it is contained in
     * as well as a JSON pointer to its position.
     * @param SpecObjectInterface $baseDocument
     * @param JsonPointer $jsonPointer
     */
    public function setDocumentContext(SpecObjectInterface $baseDocument, JsonPointer $jsonPointer);

    /**
     * @return SpecObjectInterface|null returns the base document where this object is located in.
     * Returns `null` if no context information was provided by [[setDocumentContext]].
     */
    public function getBaseDocument(): ?SpecObjectInterface;
    /**
     * @return JsonPointer|null returns a JSON pointer describing the position of this object in the base document.
     * Returns `null` if no context information was provided by [[setDocumentContext]].
     */
    public function getDocumentPosition(): ?JsonPointer;
}
