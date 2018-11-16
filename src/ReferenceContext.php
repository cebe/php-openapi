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


    public function __construct(SpecObjectInterface $base, string $uri)
    {
        $this->_baseSpec = $base;
        $this->_uri = $uri;
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
        if (!isset($parts['scheme'], $parts['host'])) {
            // TODO resolve relative URL
            throw new UnresolvableReferenceException('Relative URLs are currently not supported in Reference.');
        }

        return $uri;
    }
}
