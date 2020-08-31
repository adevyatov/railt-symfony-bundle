<?php

declare(strict_types=1);

namespace Railt\SymfonyBundle\Storage;

use Psr\SimpleCache\CacheInterface as PsrSimpleCacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Contracts\Cache\CacheInterface as SymfonyCacheInterface;

class CacheAdapter implements PsrSimpleCacheInterface
{
    private Psr16Cache $cache;

    public function __construct(SymfonyCacheInterface $symfonyCache = null)
    {
        $this->cache = new Psr16Cache($symfonyCache ?? new ArrayAdapter());
    }

    public function get($key, $default = null)
    {
        $this->cache->get($key, $default);
    }

    public function set($key, $value, $ttl = null)
    {
        $this->cache->set($key, $value, $ttl);
    }

    public function delete($key)
    {
        $this->cache->delete($key);
    }

    public function clear()
    {
        $this->cache->clear();
    }

    public function getMultiple($keys, $default = null)
    {
        $this->cache->getMultiple($keys, $default);
    }

    public function setMultiple($values, $ttl = null)
    {
        $this->cache->setMultiple($values, $ttl);
    }

    public function deleteMultiple($keys)
    {
        $this->cache->deleteMultiple($keys);
    }

    public function has($key)
    {
        $this->cache->has($key);
    }

}
