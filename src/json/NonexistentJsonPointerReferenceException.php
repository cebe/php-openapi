<?php

namespace cebe\openapi\json;

use Exception;

/**
 * NonexistentJsonPointerReferenceException represents the error condition
 * "A pointer that references a nonexistent value" of the JSON pointer specification.
 *
 * @link https://tools.ietf.org/html/rfc6901 (7. Error Handling)
 */
class NonexistentJsonPointerReferenceException extends Exception
{

}
