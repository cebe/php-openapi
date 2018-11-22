<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\SpecBaseObject;

/**
 * The Schema Object allows the definition of input and output data types.
 *
 * These types can be objects, but also primitives and arrays. This object is an extended subset of the
 * [JSON Schema Specification Wright Draft 00](http://json-schema.org/).
 *
 * For more information about the properties, see
 * [JSON Schema Core](https://tools.ietf.org/html/draft-wright-json-schema-00) and
 * [JSON Schema Validation](https://tools.ietf.org/html/draft-wright-json-schema-validation-00).
 * Unless stated otherwise, the property definitions follow the JSON Schema.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#schemaObject
 *
 * @property-read string $title
 * @property-read int|float $multipleOf
 * @property-read int|float $maximum
 * @property-read int|float $exclusiveMaximum
 * @property-read int|float $minimum
 * @property-read int|float $exclusiveMinimum
 * @property-read int $maxLength
 * @property-read int $minLength
 * @property-read string $pattern (This string SHOULD be a valid regular expression, according to the [ECMA 262 regular expression dialect](https://www.ecma-international.org/ecma-262/5.1/#sec-7.8.5))
 * @property-read int $maxItems
 * @property-read int $minItems
 * @property-read bool $uniqueItems
 * @property-read int $maxProperties
 * @property-read int $minProperties
 * @property-read string[] $required list of required properties
 * @property-read array $enum
 *
 * @property-read string $type
 * @property-read Schema[] $allOf
 * @property-read Schema[] $oneOf
 * @property-read Schema[] $anyOf
 * @property-read Schema|null $not
 * @property-read Schema|null $items
 * @property-read Schema[] $properties
 * @property-read Schema|bool $additionalProperties
 * @property-read string $description
 * @property-read string $format
 * @property-read mixed $default
 *
 * @property-read bool $nullable
 * @property-read Discriminator|null $discriminator
 * @property-read bool $readOnly
 * @property-read bool $writeOnly
 * @property-read Xml|null $xml
 * @property-read ExternalDocumentation|null $externalDocs
 * @property-read mixed $example
 * @property-read bool $deprecated
 *
 */
class Schema extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'type' => Type::STRING,
            'allOf' => [Schema::class],
            'oneOf' => [Schema::class],
            'anyOf' => [Schema::class],
            'not' => Schema::class,
            'items' => Schema::class,
            'properties' => [Type::STRING, Schema::class],
            //'additionalProperties' => 'boolean' | ['string', Schema::class], handled in constructor
            'description' => Type::STRING,
            'format' => Type::STRING,
            'default' => Type::ANY,

            'nullable' => Type::BOOLEAN,
            'discriminator' => Discriminator::class,
            'readOnly' => Type::BOOLEAN,
            'writeOnly' => Type::BOOLEAN,
            'xml' => Xml::class,
            'externalDocs' => ExternalDocumentation::class,
            'example' => Type::ANY,
            'deprecated' => Type::BOOLEAN,
        ];
    }

    /**
     * Create an object from spec data.
     * @param array $data spec data read from YAML or JSON
     * @throws TypeErrorException in case invalid data is supplied.
     */
    public function __construct(array $data)
    {
        if (isset($data['additionalProperties'])) {
            if (is_array($data['additionalProperties'])) {
                try {
                    $data['additionalProperties'] = new Schema($data['additionalProperties']);
                } catch (\TypeError $e) {
                    throw new TypeErrorException(
                        "Unable to instantiate Schema Object with data '" . print_r($data['additionalProperties'], true) . "'",
                        $e->getCode(),
                        $e
                    );
                }
            }
        } else {
            // additionalProperties defaults to true.
            $data['additionalProperties'] = true;
        }
        parent::__construct($data);
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    protected function performValidation()
    {
    }
}
