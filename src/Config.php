<?php

declare(strict_types=1);

namespace Railt\SymfonyBundle;

use DirectoryIterator;
use Railt\Foundation\Config\Repository;
use Railt\Foundation\Config\RepositoryInterface;
use Railt\SymfonyBundle\Exception\EndpointDefinitionNotFoundException;
use Railt\SymfonyBundle\Exception\SchemaDefinitionNotFoundException;

class Config
{
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
        $this->debug = $config['debug'] ?? false;
        $this->cache = $config['cache'] ?? !$this->debug;
    }

    public function getConfigRepository(string $endpoint = null): RepositoryInterface
    {
        $config = new Repository();

        if ($endpoint) {
            if (!isset($this->config['endpoints'][$endpoint])) {
                throw new EndpointDefinitionNotFoundException($endpoint, \array_keys($this->config['endpoints']));
            }

            $autoloads = [];
            foreach ($this->config['endpoints'][$endpoint]['autoload'] as $path) {
                $autoloads = \array_merge($autoloads, $this->getAutoloadFiles($path));
            }

            $config->set(RepositoryInterface::KEY_EXTENSIONS, $this->config['endpoints'][$endpoint]['extensions']);
            $config->set(RepositoryInterface::KEY_AUTOLOAD_FILES, $autoloads);

            return $config;
        }

        if ($this->config['extensions']) {
            $config->set(RepositoryInterface::KEY_EXTENSIONS, $this->config['extensions']);
        }

        $globalAutoloads = [];
        foreach ($this->config['autoload'] as $path) {
            $globalAutoloads = \array_merge($globalAutoloads, $this->getAutoloadFiles($path));
        }

        if ($globalAutoloads) {
            $config->set(RepositoryInterface::KEY_AUTOLOAD_PATHS, $this->config['autoload']);
        }

        return $config;
    }

    public function getSchemaPath(string $endpoint)
    {
        if (!\array_key_exists($endpoint, $this->config['endpoints'])) {
            throw new EndpointDefinitionNotFoundException($endpoint, \array_keys($this->config['endpoints']));
        }

        return $this->config['endpoints'][$endpoint]['schema'];
    }

    /**
     * @return bool
     */
    public function isCacheEnabled(string $endpoint): bool
    {
        if (\is_string($this->cache) && Str::lower($this->cache) === 'false') {
            return false;
        }

        return (bool) $this->cache;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        if (\is_string($this->debug) && Str::lower($this->debug) === 'false') {
            return false;
        }

        return (bool) $this->debug;
    }

    protected function getAutoloadFiles(string $path): array
    {
        $files = [];
        $directory = new DirectoryIterator($path);

        foreach ($directory as $item) {
            if ($item->isDot()) {
                continue;
            }

            if ($item->isDir()) {
                $files = \array_merge($files, $this->getAutoloadFiles($item->getPathname()));
            }

            if ($item->isFile() && \in_array($item->getExtension(), ['graphqls', 'graphql'], true)) {
                $files[] = $item->getPathname();
            }
        }

        return $files;
    }
}
