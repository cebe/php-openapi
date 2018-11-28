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
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#pathItemObject
 *
 * @property-read string $summary
 * @property-read string $description
 * @property-read Operation|null $get
 * @property-read Operation|null $put
 * @property-read Operation|null $post
 * @property-read Operation|null $delete
 * @property-read Operation|null $options
 * @property-read Operation|null $head
 * @property-read Operation|null $patch
 * @property-read Operation|null $trace
 * @property-read Server[] $servers
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
            'parameters' => [Parameter::class],
        ];
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
        // no required arguments
    }

    /**
     * Return all operations of this Path.
     * @return Operation[]
     */
    public function getOperations()
    {
        $operations = [];
        foreach (static::attributes() as $attribute => $type) {
            if ($type === Operation::class && isset($this->$attribute)) {
                $operations[$attribute] = $this->$attribute;
            }
        }
        return $operations;
    }
}
