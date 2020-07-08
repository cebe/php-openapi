<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/php-openapi/blob/master/LICENSE
 */

namespace cebe\openapi;

/**
 * ReferenceContextCache represents a cache storage for caching content of referenced files.
 */
class ReferenceContextCache
{
    private $_cache = [];
//    private $_isbase = [];


    public function set($ref, $type, $data)//, $isbase = false)
    {
        $this->_cache[$ref][$type ?? ''] = $data;

        // store fallback value for resolving with unknown type
        if ($type !== null && !isset($this->_cache[$ref][''])) {
            $this->_cache[$ref][''] = $data;
        }

//        if ($isbase) {
//            $this->_isbase[$ref] = true;
//        }
    }

//    public function isBase($ref)
//    {
//        return $this->_isbase[$ref] ?? false;
//    }

    public function get($ref, $type)
    {
        return $this->_cache[$ref][$type ?? ''] ?? null;
    }

    public function has($ref, $type)
    {
        return isset($this->_cache[$ref]) &&
            array_key_exists($type ?? '', $this->_cache[$ref]);
    }
}
