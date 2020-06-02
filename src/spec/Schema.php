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
 * @property string $title
 * @property int|float $multipleOf
 * @property int|float $maximum
 * @property bool $exclusiveMaximum
 * @property int|float $minimum
 * @property bool $exclusiveMinimum
 * @property int $maxLength
 * @property int $minLength
 * @property string $pattern (This string SHOULD be a valid regular expression, according to the [ECMA 262 regular expression dialect](https://www.ecma-international.org/ecma-262/5.1/#sec-7.8.5))
 * @property int $maxItems
 * @property int $minItems
 * @property bool $uniqueItems
 * @property int $maxProperties
 * @property int $minProperties
 * @property string[] $required list of required properties
 * @property array $enum
 *
 * @property string $type
 * @property Schema[]|Reference[] $allOf
 * @property Schema[]|Reference[] $oneOf
 * @property Schema[]|Reference[] $anyOf
 * @property Schema|Reference|null $not
 * @property Schema|Reference|null $items
 * @property Schema[]|Reference[] $properties
 * @property Schema|Reference|bool $additionalProperties
 * @property string $description
 * @property string $format
 * @property mixed $default
 *
 * @property bool $nullable
 * @property Discriminator|null $discriminator
 * @property bool $readOnly
 * @property bool $writeOnly
 * @property Xml|null $xml
 * @property ExternalDocumentation|null $externalDocs
 * @property mixed $example
 * @property bool $deprecated
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
            // The following properties are taken directly from the JSON Schema definition and follow the same specifications:
            // types from https://tools.ietf.org/html/draft-wright-json-schema-validation-00#section-4 ff.
            'title' => Type::STRING,
            'multipleOf' => Type::NUMBER,
            'maximum' => Type::NUMBER,
            'exclusiveMaximum' => Type::BOOLEAN,
            'minimum' => Type::NUMBER,
            'exclusiveMinimum' => Type::BOOLEAN,
            'maxLength' => Type::INTEGER,
            'minLength' => Type::INTEGER,
            'pattern' => Type::STRING,
            'maxItems' => Type::INTEGER,
            'minItems' => Type::INTEGER,
            'uniqueItems' => Type::BOOLEAN,
            'maxProperties' => Type::INTEGER,
            'minProperties' => Type::INTEGER,
            'required' => [Type::STRING],
            'enum' => [Type::ANY],
            // The following properties are taken from the JSON Schema definition but their definitions were adjusted to the OpenAPI Specification.
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
            // Other than the JSON Schema subset fields, the following fields MAY be used for further schema documentation:
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
     * @return array array of attributes default values.
     */
    protected function attributeDefaults(): array
    {
        return [
            'additionalProperties' => true,
            'required' => null,
            'enum' => null,
            'allOf' => null,
            'oneOf' => null,
            'anyOf' => null,
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
                $data['additionalProperties'] = $this->instantiate(Schema::class, $data['additionalProperties']);
            } elseif (!($data['additionalProperties'] instanceof Schema || $data['additionalProperties'] instanceof Reference || is_bool($data['additionalProperties']))) {
                $givenType = gettype($data['additionalProperties']);
                if ($givenType === 'object') {
                    $givenType = get_class($data['additionalProperties']);
                }
                throw new TypeErrorException(sprintf('Schema::$additionalProperties MUST be either boolean or a Schema/Reference object, "%s" given', $givenType));
            }
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
