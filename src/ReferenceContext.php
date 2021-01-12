<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

use cebe\openapi\exceptions\IOException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\json\JsonPointer;
use cebe\openapi\spec\Reference;
use Symfony\Component\Yaml\Yaml;

/**
 * ReferenceContext represents a context in which references are resolved.
 */
class ReferenceContext
{
    /**
     * only resolve external references.
     * The result will be a single API description file with references
     * inside of the file structure.
     */
    const RESOLVE_MODE_INLINE = 'inline';
    /**
     * resolve all references, except recursive ones.
     */
    const RESOLVE_MODE_ALL = 'all';

    /**
     * @var bool whether to throw UnresolvableReferenceException in case a reference can not
     * be resolved. If `false` errors are added to the Reference Objects error list instead.
     */
    public $throwException = true;
    /**
     * @var string
     */
    public $mode = self::RESOLVE_MODE_ALL;
    /**
     * @var SpecObjectInterface
     */
    private $_baseSpec;
    /**
     * @var string
     */
    private $_uri;
    /**
     * @var ReferenceContextCache
     */
    private $_cache;


    /**
     * ReferenceContext constructor.
     * @param SpecObjectInterface $base the base object of the spec.
     * @param string $uri the URI to the base object.
     * @param ReferenceContextCache $cache cache instance for storing referenced file data.
     * @throws UnresolvableReferenceException in case an invalid or non-absolute URI is provided.
     */
    public function __construct(?SpecObjectInterface $base, string $uri, $cache = null)
    {
        $this->_baseSpec = $base;
        $this->_uri = $this->normalizeUri($uri);
        $this->_cache = $cache ?? new ReferenceContextCache();
        if ($cache === null && $base !== null) {
            $this->_cache->set($this->_uri, null, $base);
        }
    }

    public function getCache(): ReferenceContextCache
    {
        return $this->_cache;
    }

    /**
     * @throws UnresolvableReferenceException in case an invalid or non-absolute URI is provided.
     */
    private function normalizeUri($uri)
    {
        if (strpos($uri, '://') !== false) {
            $parts = parse_url($uri);
            if (isset($parts['path'])) {
                $parts['path'] = $this->reduceDots($parts['path']);
            }
            return $this->buildUri($parts);
        }
        if (strncmp($uri, '/', 1) === 0) {
            $uri = $this->reduceDots($uri);
            return "file://$uri";
        }
        if (stripos(PHP_OS, 'WIN') === 0 && strncmp(substr($uri, 1), ':\\', 2) === 0) {
            $uri = $this->reduceDots($uri);
            return "file://" . strtr($uri, [' ' => '%20', '\\' => '/']);
        }
        throw new UnresolvableReferenceException('Can not resolve references for a specification given as a relative path.');
    }

    private function buildUri($parts)
    {
        $scheme   = !empty($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host     = $parts['host'] ?? '';
        $port     = !empty($parts['port']) ? ':' . $parts['port'] : '';
        $user     = $parts['user'] ?? '';
        $pass     = !empty($parts['pass']) ? ':' . $parts['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = $parts['path'] ?? '';
        $query    = !empty($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = !empty($parts['fragment']) ? '#' . $parts['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    private function reduceDots($path)
    {
        $parts = explode('/', ltrim($path, '/'));
        $c = count($parts);
        $parentOffset = 1;
        for ($i = 0; $i < $c; $i++) {
            if ($parts[$i] === '.') {
                unset($parts[$i]);
                continue;
            }
            if ($i > 0 && $parts[$i] === '..' && $parts[$i - $parentOffset] !== '..') {
                unset($parts[$i - $parentOffset]);
                unset($parts[$i]);
                $parentOffset += 2;
            }
        }
        return '/'.implode('/', $parts);
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

    public function getBaseSpec(): ?SpecObjectInterface
    {
        return $this->_baseSpec;
    }

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
        // absolute URI, no need to combine with baseURI
        if (isset($parts['scheme'])) {
            if (isset($parts['path'])) {
                $parts['path'] = $this->reduceDots($parts['path']);
            }
            return $this->buildUri($parts);
        }

        // convert absolute path on windows to a file:// URI. This is probably incomplete but should work with the majority of paths.
        if (stripos(PHP_OS, 'WIN') === 0 && strncmp(substr($uri, 1), ':\\', 2) === 0) {
            // convert absolute path on windows to a file:// URI. This is probably incomplete but should work with the majority of paths.
            $absoluteUri = "file:///" . strtr($uri, [' ' => '%20', '\\' => '/']);
            return $absoluteUri
                . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
        }

        $baseUri = $this->getUri();
        $baseParts = parse_url($baseUri);
        if (isset($parts['path'][0]) && $parts['path'][0] === '/') {
            // absolute path
            $baseParts['path'] = $this->reduceDots($parts['path']);
        } elseif (isset($parts['path'])) {
            // relative path
            $baseParts['path'] = $this->reduceDots(rtrim($this->dirname($baseParts['path'] ?? ''), '/') . '/' . $parts['path']);
        } else {
            throw new UnresolvableReferenceException("Invalid URI: '$uri'");
        }
        $baseParts['query'] = $parts['query'] ?? null;
        $baseParts['fragment'] = $parts['fragment'] ?? null;
        return $this->buildUri($baseParts);
    }

    /**
     * Fetch referenced file by URI.
     *
     * The current context will cache files by URI, so they are only loaded once.
     *
     * @throws IOException in case the file is not readable or fetching the file
     * from a remote URL failed.
     */
    public function fetchReferencedFile($uri)
    {
        if ($this->_cache->has('FILE_CONTENT://' . $uri, 'FILE_CONTENT')) {
            return $this->_cache->get('FILE_CONTENT://' . $uri, 'FILE_CONTENT');
        }

        $content = file_get_contents($uri);
        if ($content === false) {
            $e = new IOException("Failed to read file: '$uri'");
            $e->fileName = $uri;
            throw $e;
        }
        // TODO lazy content detection, should be improved
        if (strpos(ltrim($content), '{') === 0) {
            $parsedContent = json_decode($content, true);
        } else {
            $parsedContent = Yaml::parse($content);
        }
        $this->_cache->set('FILE_CONTENT://' . $uri, 'FILE_CONTENT', $parsedContent);
        return $parsedContent;
    }

    /**
     * Retrieve the referenced data via JSON pointer.
     *
     * This function caches referenced data to make sure references to the same
     * data structures end up being the same object instance in PHP.
     *
     * @param string $uri
     * @param JsonPointer $pointer
     * @param array $data
     * @param string|null $toType
     * @return SpecObjectInterface|array|null
     */
    public function resolveReferenceData($uri, JsonPointer $pointer, $data, $toType)
    {
        $ref = $uri . '#' . $pointer->getPointer();
        if ($this->_cache->has($ref, $toType)) {
            return $this->_cache->get($ref, $toType);
        }

        $referencedData = $pointer->evaluate($data);

        if ($referencedData === null) {
            return null;
        }

        // transitive reference
        if (isset($referencedData['$ref'])) {
            return new Reference($referencedData, $toType);
        } else {
            /** @var SpecObjectInterface|array $referencedObject */
            $referencedObject = $toType !== null ? new $toType($referencedData) : $referencedData;
        }

        $this->_cache->set($ref, $toType, $referencedObject);

        return $referencedObject;
    }
}
