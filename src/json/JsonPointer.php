<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\json;

/**
 * Represents a JSON Pointer (RFC 6901)
 *
 * A JSON Pointer only works in the context of a single JSON document,
 * if you need to reference values in external documents, use [[JsonReference]] instead.
 *
 * @link https://tools.ietf.org/html/rfc6901
 * @see JsonReference
 */
final class JsonPointer
{
    /**
     * @var string
     */
    private $_pointer;

    /**
     * JSON Pointer constructor.
     * @param string $pointer The JSON Pointer.
     * Must be either an empty string (for referencing the whole document), or a string starting with `/`.
     * @throws InvalidJsonPointerSyntaxException in case an invalid JSON pointer string is passed
     */
    public function __construct(string $pointer)
    {
        if (!preg_match('~^(/[^/]*)*$~', $pointer)) {
            throw new InvalidJsonPointerSyntaxException("Invalid JSON Pointer syntax: $pointer");
        }
        $this->_pointer = $pointer;
    }

    public function __toString()
    {
        return $this->_pointer;
    }

    /**
     * @return string returns the JSON Pointer.
     */
    public function getPointer(): string
    {
        return $this->_pointer;
    }

    /**
     * @return array the JSON pointer path as array.
     */
    public function getPath(): array
    {
        if ($this->_pointer === '') {
            return [];
        }
        $pointer = substr($this->_pointer, 1);
        return array_map([get_class($this), 'decode'], explode('/', $pointer));
    }

    /**
     * Append a new part to the JSON path.
     * @param string $subpath the path element to append.
     * @return JsonPointer a new JSON pointer pointing to the subpath.
     */
    public function append(string $subpath): JsonPointer
    {
        return new JsonPointer($this->_pointer . '/' . static::encode($subpath));
    }

    /**
     * Returns a JSON pointer to the parent path element of this pointer.
     * @return JsonPointer|null a new JSON pointer pointing to the parent element
     * or null if this pointer already points to the document root.
     */
    public function parent(): ?JsonPointer
    {
        $path = $this->getPath();
        if (empty($path)) {
            return null;
        }
        array_pop($path);
        if (empty($path)) {
            return new JsonPointer('');
        }
        return new JsonPointer('/' . implode('/', array_map([get_class($this), 'encode'], $path)));
    }

    /**
     * Evaluate the JSON Pointer on the provided document.
     *
     * Note that this does only resolve the JSON Pointer, it will not load external
     * documents by URI. Loading the Document from the URI is supposed to be done outside of this class.
     *
     * @param mixed $jsonDocument
     * @return mixed
     * @throws NonexistentJsonPointerReferenceException
     */
    public function evaluate($jsonDocument)
    {
        $currentReference = $jsonDocument;
        $currentPath = '';

        foreach ($this->getPath() as $part) {
            if (is_array($currentReference)) {
//                if (!preg_match('~^([1-9]*[0-9]|-)$~', $part)) {
//                    throw new NonexistentJsonPointerReferenceException(
//                        "Failed to evaluate pointer '$this->_pointer'. Invalid pointer path '$part' for Array at path '$currentPath'."
//                    );
//                }
                if ($part === '-' || !array_key_exists($part, $currentReference)) {
                    throw new NonexistentJsonPointerReferenceException(
                        "Failed to evaluate pointer '$this->_pointer'. Array has no member $part at path '$currentPath'."
                    );
                }
                $currentReference = $currentReference[$part];
            } elseif ($currentReference instanceof \ArrayAccess) {
                if (!$currentReference->offsetExists($part)) {
                    throw new NonexistentJsonPointerReferenceException(
                        "Failed to evaluate pointer '$this->_pointer'. Array has no member $part at path '$currentPath'."
                    );
                }
                $currentReference = $currentReference[$part];
            } elseif (is_object($currentReference)) {
                if (!isset($currentReference->$part) && !property_exists($currentReference, $part)) {
                    throw new NonexistentJsonPointerReferenceException(
                        "Failed to evaluate pointer '$this->_pointer'. Object has no member $part at path '$currentPath'."
                    );
                }
                $currentReference = $currentReference->$part;
            } else {
                throw new NonexistentJsonPointerReferenceException(
                    "Failed to evaluate pointer '$this->_pointer'. Value at path '$currentPath' is neither an array nor an object."
                );
            }

            $currentPath = "$currentPath/$part";
        }

        return $currentReference;
    }

    /**
     * Encodes a string for use inside of a JSON pointer.
     */
    public static function encode(string $string): string
    {
        return strtr($string, [
            '~' => '~0',
            '/' => '~1',
        ]);
    }

    /**
     * Decodes a string used inside of a JSON pointer.
     */
    public static function decode(string $string): string
    {
        return strtr($string, [
            '~1' => '/',
            '~0' => '~',
        ]);
    }
}
