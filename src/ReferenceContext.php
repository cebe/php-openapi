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
     * @var bool whether to throw UnresolvableReferenceException in case a reference can not
     * be resolved. If `false` errors are added to the Reference Objects error list instead.
     */
    public $throwException = true;
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
    public function __construct(?SpecObjectInterface $base, string $uri)
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
        if (stripos(PHP_OS, 'WIN') === 0 && strncmp(substr($uri, 1), ':\\', 2) === 0) {
            return "file:///" . strtr($uri, [' ' => '%20', '\\' => '/']);
        }
        throw new UnresolvableReferenceException('Can not resolve references for a specification given as a relative path.');
    }

    /**
     * @return mixed
     */
    public function getBaseSpec(): ?SpecObjectInterface
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
            // convert absolute path on windows to a file:// URI. This is probably incomplete but should work with the majority of paths.
            if (stripos(PHP_OS, 'WIN') === 0 && strncmp(substr($uri, 1), ':\\', 2) === 0) {
                return "file:///" . strtr($uri, [' ' => '%20', '\\' => '/']);
            }

            if (isset($parts['path'])) {
                // relative path
                return $this->dirname($baseUri) . '/' . $parts['path'];
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
            $absoluteUri .= rtrim($this->dirname($baseParts['path'] ?? ''), '/') . '/' . $parts['path'];
        }
        return $absoluteUri
            . (isset($parts['query']) ? '?' . $parts['query'] : '')
            . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
    }

    /**
     * Returns parent directory's path.
     * This method is similar to `dirname()` except that it will treat
     * both \ and / as directory separators, independent of the operating system.
     *
     * @param string $path A path string.
     * @return string the parent directory's path.
     * @see http://www.php.net/manual/en/function.dirname.php
     * @see https://github.com/yiisoft/yii2/blob/e1f6761dfd9eba1ff1260cd37b04936aaa4959b5/framework/helpers/BaseStringHelper.php#L75-L92
     */
    private function dirname($path)
    {
        $pos = mb_strrpos(str_replace('\\', '/', $path), '/');
        if ($pos !== false) {
            return mb_substr($path, 0, $pos);
        }
        return '';
    }
}
