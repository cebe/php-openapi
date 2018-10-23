<?php

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * The Header Object follows the structure of the Parameter Object with the following changes:
 *
 * 1. name MUST NOT be specified, it is given in the corresponding headers map.
 * 2. in MUST NOT be specified, it is implicitly in header.
 * 3. All traits that are affected by the location MUST be applicable to a location of header (for example, style).
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#headerObject
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class Header
{
    // TODO implement

}
