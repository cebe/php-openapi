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
 * @property Schema|Reference|null $schema
 * @property mixed $example
 * @property Example[]|Reference[] $examples
 * @property Encoding[] $encoding
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
                if ($encodingData instanceof Encoding) {
                    $encoding[$property] = $encodingData;
                } elseif (is_array($encodingData)) {
                    $schema = $this->schema->properties[$property] ?? null;
                    // Don't pass the schema if it's still an unresolved reference.
                    if ($schema instanceof Reference) {
                        $encoding[$property] = new Encoding($encodingData);
                    } else {
                        $encoding[$property] = new Encoding($encodingData, $schema);
                    }
                } else {
                    $givenType = gettype($encodingData);
                    if ($givenType === 'object') {
                        $givenType = get_class($encodingData);
                    }
                    throw new TypeErrorException(sprintf('Encoding MUST be either array or Encoding object, "%s" given', $givenType));
                }
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
