<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

/**
 * Make raw spec data available to the implementing classes
 */
interface RawSpecDataInterface
{
    public function getRawSpecData(): array;
}
