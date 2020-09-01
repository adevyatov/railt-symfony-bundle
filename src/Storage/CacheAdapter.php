<?php

declare(strict_types=1);

namespace Railt\SymfonyBundle\Storage;

use Psr\SimpleCache\CacheInterface as PsrSimpleCacheInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Contracts\Cache\CacheInterface as SymfonyCacheInterface;

class CacheAdapter implements CacheAdapterInterface
{
    private Psr16Cache $cache;

    public function __construct(SymfonyCacheInterface $symfonyCache = null)
    {
        $this->cache = new Psr16Cache($symfonyCache ?? new ArrayAdapter());
    }

    public function get($key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    public function set($key, $value, $ttl = null)
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    public function clear()
    {
        return $this->cache->clear();
    }

    public function getMultiple($keys, $default = null)
    {
        return $this->cache->getMultiple($keys, $default);
    }

    public function setMultiple($values, $ttl = null)
    {
        return $this->cache->setMultiple($values, $ttl);
    }

    public function deleteMultiple($keys)
    {
        return $this->cache->deleteMultiple($keys);
    }

    public function has($key)
    {
        return $this->cache->has($key);
    }

}
