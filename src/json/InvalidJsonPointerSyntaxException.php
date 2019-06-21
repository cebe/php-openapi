<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\json;

use Exception;

/**
 * InvalidJsonPointerSyntaxException represents the error condition "Invalid pointer syntax" of the JSON pointer specification.
 *
 * @link https://tools.ietf.org/html/rfc6901 (7. Error Handling)
 */
class InvalidJsonPointerSyntaxException extends Exception
{
}
