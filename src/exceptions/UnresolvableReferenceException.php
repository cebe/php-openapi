<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\exceptions;

use cebe\openapi\json\JsonPointer;

/**
 * This exception is thrown on attempt to resolve a reference which points to a non-existing target.
 */
class UnresolvableReferenceException extends \Exception
{
    /**
     * @var JsonPointer|null may contain context information in form of a JSON pointer to the position
     * of the broken reference in the document.
     */
    public $context;
}
