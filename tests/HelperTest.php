<?php

use cebe\openapi\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function testArrayMergeRecursiveDistinct()
    {
        $result = Helper::arrayMergeRecursiveDistinct(['id', 'name'], ['id2', 'name2']);
        $this->assertSame(['id', 'name', 'id2', 'name2'], $result);

        $result = Helper::arrayMergeRecursiveDistinct(['id', 'name'], ['id2', 'name']);
        $this->assertSame(['id', 'name', 'id2'], $result);

        $result = Helper::arrayMergeRecursiveDistinct(['type' => 'object'], ['x-faker' => true]);
        $this->assertSame(['type' => 'object', 'x-faker' => true], $result);

        $result = Helper::arrayMergeRecursiveDistinct([
            'properties' => [
                'id' => [
                    'type' => 'integer'
                ],
                'name' => [
                    'type' => 'string'
                ],
            ]
        ], [
            'properties' => [
                'id2' => [
                    'type' => 'integer'
                ],
                'name2' => [
                    'type' => 'string'
                ],
            ]
        ]);
        $this->assertSame([
            'properties' => [
                'id' => [
                    'type' => 'integer'
                ],
                'name' => [
                    'type' => 'string'
                ],
                'id2' => [
                    'type' => 'integer'
                ],
                'name2' => [
                    'type' => 'string'
                ],
            ]
        ], $result);

        $result = Helper::arrayMergeRecursiveDistinct([
            'properties' => [
                'id' => [
                    'type' => 'integer'
                ],
                'name' => [
                    'type' => 'string',
                    'maxLength' => 10
                ],
            ]
        ], [
            'properties' => [
                'id2' => [
                    'type' => 'integer'
                ],
                'name' => [
                    'type' => 'string',
                    'maxLength' => 12
                ],
            ]
        ]);
        $this->assertSame([
            'properties' => [
                'id' => [
                    'type' => 'integer'
                ],
                'name' => [
                    'type' => 'string',
                    'maxLength' => 12
                ],
                'id2' => [
                    'type' => 'integer'
                ]
            ]
        ], $result);
    }

}
