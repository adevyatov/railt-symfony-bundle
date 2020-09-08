<?php

declare(strict_types=1);

namespace Railt\SymfonyBundle;

use Illuminate\Support\Arr;
use Psr\Container\ContainerInterface;
use Railt\Foundation\Application;
use Railt\Foundation\Config\Repository;
use Railt\Foundation\Config\RepositoryInterface;
use Railt\SymfonyBundle\Exception\SchemaDefinitionNotFoundException;

class Config
{
    public const NODE_ROOT = 'railt';
    public const NODE_DEBUG = 'debug';
    public const NODE_CACHE = 'cache';
    public const NODE_AUTOLOAD = 'autoload';
    public const NODE_EXTENSIONS = 'extensions';

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var bool
     */
    private $cache;

    /**
     * @var array
     */
    private $config;

    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->debug = $config[static::NODE_DEBUG] ?? false;
        $this->cache = $config[static::NODE_CACHE] ?? ! $this->debug;
    }

    public function getConfig(string $key = null)
    {
        return Arr::get($this->config, $key);
    }

    public function getRepository(): RepositoryInterface
    {
        $repository = new Repository();
        $repository->set(self::NODE_AUTOLOAD . '.paths', $this->config[self::NODE_AUTOLOAD]);
        $repository->set(self::NODE_EXTENSIONS, $this->config[self::NODE_EXTENSIONS]);

        return $repository;
    }

    public function getSchemaPath(string $schema)
    {
        if (!\array_key_exists($schema, $this->config['schemas'])) {
            $allowed = \array_keys($this->config['schemas']);
            throw new SchemaDefinitionNotFoundException($schema, $allowed);
        }

        return $this->config['schemas'][$schema];
    }

    /**
     * @return bool
     */
    public function isCacheEnabled(): bool
    {
        if (\is_string($this->cache) && Str::lower($this->cache) === 'false') {
            return false;
        }

        return (bool)$this->cache;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        if (\is_string($this->debug) && Str::lower($this->debug) === 'false') {
            return false;
        }

        return (bool)$this->debug;
    }
}
