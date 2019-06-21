<?php

namespace cebe\openapi\json;


use Exception;

/**
 * MalformedJsonReferenceObjectException is thrown if a JSON Reference Object does not contain the "$ref" member.
 *
 * @link https://tools.ietf.org/html/draft-pbryan-zyp-json-ref-03 (3. Syntax)
 */
class MalformedJsonReferenceObjectException extends Exception
{
}
