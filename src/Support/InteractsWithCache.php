<?php

/*
 * This file is part of the jimchen/bazhuoyu-api-client.
 *
 * (c) JimChen <18219111672@163.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Bazhuayu\Support;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;

trait InteractsWithCache
{
    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected $cache;

    /**
     * Get cache instance.
     *
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function getCache()
    {
        if ($this->cache) {
            return $this->cache;
        }

        return $this->cache = $this->createDefaultCache();
    }

    /**
     * Set cache instance.
     *
     * @param \Psr\SimpleCache\CacheInterface $cache
     *
     * @return $this
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @return \Symfony\Component\Cache\Simple\FilesystemCache
     */
    protected function createDefaultCache()
    {
        return new FilesystemCache();
    }
}
