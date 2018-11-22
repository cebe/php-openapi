<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

use cebe\openapi\exceptions\UnresolvableReferenceException;

/**
 * ReferenceContext represents a context in which references are resolved.
 */
class ReferenceContext
{
    /**
     * @var SpecObjectInterface
     */
    private $_baseSpec;
    /**
     * @var string
     */
    private $_uri;

    /**
     * ReferenceContext constructor.
     * @param SpecObjectInterface $base the base object of the spec.
     * @param string $uri the URI to the base object.
     * @throws UnresolvableReferenceException in case an invalid or non-absolute URI is provided.
     */
    public function __construct(SpecObjectInterface $base, string $uri)
    {
        $this->_baseSpec = $base;
        $this->_uri = $this->normalizeUri($uri);
    }

    /**
     * @throws UnresolvableReferenceException in case an invalid or non-absolute URI is provided.
     */
    private function normalizeUri($uri)
    {
        if (strpos($uri, '://') !== false) {
            return $uri;
        }
        if (strncmp($uri, '/', 1) === 0) {
            return "file://$uri";
        }
        throw new UnresolvableReferenceException('Can not resolve references for a specification given as a relative path.');
    }

    /**
     * @return mixed
     */
    public function getBaseSpec(): SpecObjectInterface
    {
        return $this->_baseSpec;
    }

    /**
     * @return mixed
     */
    public function getUri(): string
    {
        return $this->_uri;
    }

    /**
     * Resolve a relative URI to an absolute URI in the current context.
     * @param string $uri
     * @throws UnresolvableReferenceException
     * @return string
     */
    public function resolveRelativeUri(string $uri): string
    {
        $parts = parse_url($uri);
        if (isset($parts['scheme'])) {
            // absolute URL
            return $uri;
        }

        $baseUri = $this->getUri();
        if (strncmp($baseUri, 'file://', 7) === 0) {
            if (isset($parts['path'][0]) && $parts['path'][0] === '/') {
                // absolute path
                return 'file://' . $parts['path'];
            }
            if (isset($parts['path'])) {
                // relative path
                return dirname($baseUri) . '/' . $parts['path'];
            }

            throw new UnresolvableReferenceException("Invalid URI: '$uri'");
        }

        $baseParts = parse_url($baseUri);
        $absoluteUri = implode('', [
            $baseParts['scheme'],
            '://',
            isset($baseParts['username']) ? $baseParts['username'] . (
                isset($baseParts['password']) ? ':' . $baseParts['password'] : ''
            ) . '@' : '',
            $baseParts['host'] ?? '',
            isset($baseParts['port']) ? ':' . $baseParts['port'] : '',
        ]);
        if (isset($parts['path'][0]) && $parts['path'][0] === '/') {
            $absoluteUri .= $parts['path'];
        } elseif (isset($parts['path'])) {
            $absoluteUri .= rtrim(dirname($baseParts['path'] ?? ''), '/') . '/' . $parts['path'];
        }
        return $absoluteUri
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }
}
