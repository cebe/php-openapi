<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\json;

use JsonSerializable;

/**
 * Represents a JSON Reference (IETF draft-pbryan-zyp-json-ref-03)
 *
 * Includes the URI to another JSON document and the JSON Pointer as
 * the fragment section of the URI.
 *
 * @link https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03
 * @see JsonPointer
 */
final class JsonReference implements JsonSerializable
{
    /**
     * @var string
     */
    private $_uri = '';
    /**
     * @var JsonPointer
     */
    private $_pointer;

    /**
     * Create a JSON Reference instance from a JSON document.
     * @param string $json the JSON object, e.g. `{ "$ref": "http://example.com/example.json#/foo/bar" }`.
     * @return JsonReference
     * @throws MalformedJsonReferenceObjectException
     * @throws InvalidJsonPointerSyntaxException if an invalid JSON pointer string is passed as part of the fragment section.
     */
    public static function createFromJson(string $json): JsonReference
    {
        $refObject = json_decode($json, true);
        if (!isset($refObject['$ref'])) {
            throw new MalformedJsonReferenceObjectException('JSON Reference Object must contain the "$ref" member.');
        }
        return static::createFromReference($refObject['$ref']);
    }

    /**
     * Create a JSON Reference instance from an URI and a JSON Pointer.
     * If no JSON Pointer is given this will be interpreted as an empty string JSON pointer, which
     * references the whole document.
     * @param string $uri the URI to the document without a fragment part.
     * @param JsonPointer $jsonPointer
     * @return JsonReference
     */
    public static function createFromUri(string $uri, ?JsonPointer $jsonPointer = null): JsonReference
    {
        $jsonReference = static::createFromReference($uri);
        $jsonReference->_pointer = $jsonPointer ?: new JsonPointer('');
        return $jsonReference;
    }

    /**
     * Create a JSON Reference instance from a reference URI.
     * @param string $referenceURI the JSON Reference URI, e.g. `"http://example.com/example.json#/foo/bar"`.
     * @return JsonReference
     * @throws InvalidJsonPointerSyntaxException if an invalid JSON pointer string is passed as part of the fragment section.
     */
    public static function createFromReference(string $referenceURI): JsonReference
    {
        $jsonReference = new JsonReference();
        if (strpos($referenceURI, '#') !== false) {
            list($uri, $fragment) = explode('#', $referenceURI, 2);
            $jsonReference->_uri = $uri;
            $jsonReference->_pointer = new JsonPointer(rawurldecode($fragment));
        } else {
            $jsonReference->_uri = $referenceURI;
            $jsonReference->_pointer = new JsonPointer('');
        }
        return $jsonReference;
    }

    private function __construct()
    {
    }

    public function __clone()
    {
        $this->_pointer = clone $this->_pointer;
    }


    public function getJsonPointer(): JsonPointer
    {
        return $this->_pointer;
    }

    /**
     * @return string returns the URI of the referenced JSON document without the fragment (JSON Pointer) part.
     */
    public function getDocumentUri(): string
    {
        return $this->_uri;
    }

    /**
     * @return string returns the JSON Pointer in URI format.
     */
    public function getReference(): string
    {
        // https://tools.ietf.org/html/rfc6901#section-6
        // A JSON Pointer can be represented in a URI fragment identifier by
        // encoding it into octets using UTF-8 [RFC3629], while percent-encoding
        // those characters not allowed by the fragment rule in [RFC3986].
        // https://tools.ietf.org/html/rfc3986#page-25
        // The characters slash ("/") and question mark ("?") are allowed to
        // represent data within the fragment identifier.
        // https://tools.ietf.org/html/rfc3986#section-2.4
        // the "%7E" can be replaced by "~" without changing its interpretation.
        return $this->_uri . '#' . strtr(rawurlencode($this->_pointer->getPointer()), ['%2F' => '/', '%3F' => '?', '%7E' => '~']);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return (object)['$ref' => $this->getReference()];
    }
}
