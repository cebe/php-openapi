<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\exceptions;

/**
 * This exception is thrown if the input data from OpenAPI spec
 * provides data in another type that is expected.
 *
 */
class TypeErrorException extends \Exception
{
}
