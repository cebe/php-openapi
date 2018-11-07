<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\SpecBaseObject;

/**
 * Describes the operations available on a single path.
 *
 * A Path Item MAY be empty, due to ACL constraints. The path itself is still exposed to the documentation
 * viewer but they will not know which operations and parameters are available.
 *
 * // @TODO $ref
 * @property-read string $summary
 * @property-read string $description
 * @property-read Operation $get
 * @property-read Operation $put
 * @property-read Operation $post
 * @property-read Operation $delete
 * @property-read Operation $options
 * @property-read Operation $head
 * @property-read Operation $patch
 * @property-read Operation $trace
 * @property-read Server $servers
 * @property-read Parameter[]|Reference[] $parameters
 *
 */
class PathItem extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            // '$ref' => TYPE::REFERENCE,
            'summary' => Type::STRING,
            'description' => Type::STRING,
            'get' => Operation::class,
            'put' => Operation::class,
            'post' => Operation::class,
            'delete' => Operation::class,
            'options' => Operation::class,
            'head' => Operation::class,
            'patch' => Operation::class,
            'trace' => Operation::class,
            'servers' => [Server::class],
            'parameters' => [Parameter::class], // @TODO Reference::class
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
        // no required arguments
    }
}
