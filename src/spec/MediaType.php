<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\SpecBaseObject;

/**
 * Each Media Type Object provides schema and examples for the media type identified by its key.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#mediaTypeObject
 *
 * @property-read Schema|Reference|null $schema
 * @property-read mixed $example
 * @property-read Example[]|Reference[] $examples
 * @property-read Encoding[] $encoding
 */
class MediaType extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'schema' => Schema::class,
            'example' => Type::ANY,
            'examples' => [Type::STRING, Example::class],
            'encoding' => [Type::STRING, Encoding::class],
        ];
    }

    /**
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     * @throws TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data)
    {
        // instantiate Encoding by passing the schema for extracting default values
        $encoding = $data['encoding'] ?? null;
        unset($data['encoding']);

        parent::__construct($data);

        if (!empty($encoding)) {
            foreach ($encoding as $property => $encodingData) {
                $encoding[$property] = new Encoding($encodingData, $this->schema->properties[$property] ?? null);
            }
            $this->encoding = $encoding;
        }
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
    }
}
