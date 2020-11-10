<?php

/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\SymfonyBundle;

use Railt\Foundation\Application;
use Railt\Foundation\Config\RepositoryInterface;
use Railt\SymfonyBundle\Storage\CacheAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class RailtExtension
 */
class RailtExtension extends ConfigurableExtension
{
    /**
     * @var string An configuration
     */
    private const CONFIGURATION_ROOT_NODE = 'railt';

    /**
     * @var string Get debug configuration parameter
     */
    private const CONFIGURATION_DEBUG_PARAMETER = 'kernel.debug';

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     * @return RailtConfiguration|mixed|null|\Symfony\Component\Config\Definition\ConfigurationInterface
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new RailtConfiguration(self::CONFIGURATION_ROOT_NODE, $this->isDebug($container));
    }

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function loadInternal(array $configs, ContainerBuilder $container): void
    {
        $root = self::CONFIGURATION_ROOT_NODE . '.';

        $container->setParameter($root, $configs);
        $container->setParameter($root . 'debug', $configs['debug']);
        $container->setParameter($root . RepositoryInterface::KEY_AUTOLOAD_PATHS, $configs['autoload']);
        $container->setParameter($root . RepositoryInterface::KEY_AUTOLOAD_EXTENSIONS, $configs['extensions']);

        foreach ($configs['endpoints'] as $name => $config) {
            $container->setParameter($root . '.endpoints.' . $name, $config);
        }

        // cache
        $cacheRef = null;
        if ($configs['cache'] ?? false) {
            $cacheRef = new Reference($configs['cache']);
        }

        $cache = new Definition(CacheAdapter::class, [$cacheRef]);
        $container->setDefinition(CacheAdapter::class, $cache);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * @param ContainerBuilder $container
     * @return bool
     */
    private function isDebug(ContainerBuilder $container): bool
    {
        if ($container->hasParameter(self::CONFIGURATION_DEBUG_PARAMETER)) {
            return (bool)$container->getParameter(self::CONFIGURATION_DEBUG_PARAMETER);
        }

        return false;
    }
}
