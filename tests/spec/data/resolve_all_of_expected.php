<?php

return [
    'type' => 'object',
    'properties' => [
        'id' => [
            'type' => 'integer'
        ],
        'content' => [
            'type' => 'string'
        ],
        'user' => [
            'required' => [
                'id',
                'name',
                'id2',
                'name2',
                'id3'
            ],
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer'
                ],
                'name' => [
                    'maxLength' => 14,
                    'type' => 'string'
                ],
                'id2' => [
                    'type' => 'integer'
                ],
                'name2' => [
                    'maxLength' => 12,
                    'type' => 'string'
                ],
                'id3' => [
                    'type' => 'integer'
                ],
                'id4' => [
                    'type' => 'integer'
                ],
                'name4' => [
                    'type' => 'string'
                ]

            ],
            'x-faker' => false,
            'x-faker2' => true
        ],
    ],
];
