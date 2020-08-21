<?php

declare(strict_types=1);

namespace Railt\SymfonyBundle;

use Psr\Container\ContainerInterface;

class Config
{
    /**
     * @var string
     */
    public const ROOT_NODE = 'railt';

    /**
     * @var string
     */
    public const DEBUG_NODE = 'debug';

    /**
     * @var string
     */
    public const CACHE_IS_ENABLED = 'cache';

    /**
     * @var string
     */
    private const ENDPOINTS_NODE = 'endpoints';

    /**
     * @var string
     */
    private const PLAYGROUND_NODE = 'playground';

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var Playground
     */
    private $graphiql;

    /**
     * @var bool
     */
    private $cache;

    /**
     * Config constructor.
     *
     * @param array $config
     */
    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $config = $container->getParameter('railt');

        $this->debug = $config[static::DEBUG_NODE] ?? false;

        foreach ($config[static::ENDPOINTS_NODE] ?? [] as $name => $endpoint) {
            $this->endpoints[$name] = new Endpoint($this, $name, $endpoint);
        }

        //$this->graphiql = new Playground($this, (array)($config[static::PLAYGROUND_NODE] ?? []));

        $this->cache = $config[static::CACHE_IS_ENABLED] ?? ! $this->debug;
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

    /**
     * @param Registrar $registrar
     */
    public function register(Registrar $registrar): void
    {
        foreach ($this->getEndpoints() as $endpoint) {
            $endpoint->register($registrar);
        }

        $this->getPlayground()->register($registrar);
    }

    ///**
    // * @return Playground
    // */
    //public function getPlayground(): Playground
    //{
    //    return $this->graphiql;
    //}
}
