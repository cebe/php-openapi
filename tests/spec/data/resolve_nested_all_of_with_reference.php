<?php
return [
    'required' => [
        'id2',
        'name',
    ],
    'type' => 'object',
    'properties' => [
        'id2' => [
            'type' => 'integer',
        ],
        'name' => [
            'maxLength' => 12,
            'type' => 'string',
        ],
        'physical' => [
            'type' => 'object',
            'properties' => [
                'weight' => [
                    'type' => 'integer',
                ],
                'dimension' => [
                    'type' => 'object',
                    'properties' => [
                        'height' => [
                            'type' => 'integer',
                        ],
                        'length' => [
                            'type' => 'integer',
                        ],
                        'width' => [
                            'type' => 'integer',
                        ],
                        'miscellaneous' => [
                            'type' => 'object',
                            'properties' => [
                                'owner' => [
                                    'required' => [
                                        0 => 'id',
                                        1 => 'name',
                                    ],
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => [
                                            'type' => 'integer',
                                        ],
                                        'name' => [
                                            'maxLength' => 10,
                                            'type' => 'string',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];