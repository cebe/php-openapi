<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\exceptions;

/**
 * This exception is thrown when reading or writing of a file fails.
 * @since 1.2.1
 */
class IOException extends \Exception
{
    /**
     * @var string|null if available, the name of the affected file.
     */
    public $fileName;
}
