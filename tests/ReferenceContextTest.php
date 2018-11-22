<?php


use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;

class ReferenceContextTest extends \PHPUnit\Framework\TestCase
{
    public function uriProvider()
    {
        $data = [
            [
                'https://example.com/openapi.yaml', // base URI
                'definitions.yaml', // referenced URI
                'https://example.com/definitions.yaml', // expected result
            ],
            [
                'https://example.com/openapi.yaml', // base URI
                '/definitions.yaml', // referenced URI
                'https://example.com/definitions.yaml', // expected result
            ],
            [
                'https://example.com/api/openapi.yaml', // base URI
                'definitions.yaml', // referenced URI
                'https://example.com/api/definitions.yaml', // expected result
            ],
            [
                'https://example.com/api/openapi.yaml', // base URI
                '/definitions.yaml', // referenced URI
                'https://example.com/definitions.yaml', // expected result
            ],
            [
                'https://example.com/api/openapi.yaml', // base URI
                '../definitions.yaml', // referenced URI
                'https://example.com/api/../definitions.yaml', // expected result
            ],
            [
                '/var/www/openapi.yaml', // base URI
                'definitions.yaml', // referenced URI
                'file:///var/www/definitions.yaml', // expected result
            ],
            [
                '/var/www/openapi.yaml', // base URI
                '/var/definitions.yaml', // referenced URI
                'file:///var/definitions.yaml', // expected result
            ],

        ];

        // absolute URLs should not be changed
        foreach(array_unique(array_map('current', $data)) as $url) {
            $data[] = [
                $url,
                'file:///var/www/definitions.yaml',
                'file:///var/www/definitions.yaml',
            ];
            $data[] = [
                $url,
                'https://example.com/definitions.yaml',
                'https://example.com/definitions.yaml',
            ];
        }

        return $data;
    }

    /**
     * @dataProvider uriProvider
     */
    public function testResolveUri($baseUri, $referencedUri, $expected)
    {
        $context = new ReferenceContext(new OpenApi([]), $baseUri);
        $this->assertEquals($expected, $context->resolveRelativeUri($referencedUri));
    }

}
