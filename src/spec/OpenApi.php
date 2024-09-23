<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi\spec;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\SpecBaseObject;

/**
 * This is the root document object of the OpenAPI document.
 *
 * @link https://github.com/OAI/OpenAPI-Specification/blob/3.0.2/versions/3.0.2.md#openapi-object
 *
 * @property string $openapi
 * @property Info $info
 * @property Server[] $servers
 * @property Paths|PathItem[] $paths
 * @property Components|null $components
 * @property SecurityRequirement[] $security
 * @property Tag[] $tags
 * @property ExternalDocumentation|null $externalDocs
 *
 */
class OpenApi extends SpecBaseObject
{
    /**
     * @return array array of attributes available in this object.
     */
    protected function attributes(): array
    {
        return [
            'openapi' => Type::STRING,
            'info' => Info::class,
            'servers' => [Server::class],
            'paths' => Paths::class,
            'components' => Components::class,
            'security' => [SecurityRequirement::class],
            'tags' => [Tag::class],
            'externalDocs' => ExternalDocumentation::class,
        ];
    }

    /**
     * @return array array of attributes default values.
     */
    protected function attributeDefaults(): array
    {
        return [
            // Spec: If the servers property is not provided, or is an empty array,
            // the default value would be a Server Object with a url value of /.
            'servers' => [
                new Server(['url' => '/'])
            ],
        ];
    }

    public function __get($name)
    {
        $ret = parent::__get($name);
        // Spec: If the servers property is not provided, or is an empty array,
        // the default value would be a Server Object with a url value of /.
        if ($name === 'servers' && $ret === []) {
            return $this->attributeDefaults()['servers'];
        }
        return $ret;
    }

    /**
     * Perform validation on this object, check data against OpenAPI Specification rules.
     */
    public function performValidation()
    {
        $this->requireProperties(['openapi', 'info', 'paths']);
        if (!empty($this->openapi) && !preg_match('/^3\.0\.\d+(-rc\d)?$/i', $this->openapi)) {
            $this->addError('Unsupported openapi version: ' . $this->openapi);
        }
    }

    /**
     * Thanks https://www.php.net/manual/en/function.array-merge-recursive.php#96201
     *
     * Merges any number of arrays / parameters recursively, replacing
     * entries with string keys with values from latter arrays.
     * If the entry or the next value to be assigned is an array, then it
     * automagically treats both arguments as an array.
     * Numeric entries are appended, not replaced, but only if they are
     * unique
     *
     * Function call example: `$result = array_merge_recursive_distinct(a1, a2, ... aN);`
     * TODO add test and more docs
     */
    public static function arrayMergeRecursiveDistinct()
    {
        $arrays = func_get_args();
        $base = array_shift($arrays);
        if(!is_array($base)) {
            $base = empty($base) ? [] : [$base];
        }
        foreach($arrays as $append) {
            if(!is_array($append)) {
                $append = [$append];
            }
            foreach($append as $key => $value) {
                if(!array_key_exists($key, $base) and !is_numeric($key)) {
                    $base[$key] = $append[$key];
                    continue;
                }
                if(is_array($value) || is_array($base[$key])) {
                    $base[$key] = static::arrayMergeRecursiveDistinct($base[$key], $append[$key]);
                } elseif(is_numeric($key)) {
                    if(!in_array($value, $base)) {
                        $base[] = $value;
                    }
                } else {
                    $base[$key] = $value;
                }
            }
        }
        return $base;
    }
}
