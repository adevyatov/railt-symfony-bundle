<?php

/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\SymfonyBundle\Controller;

use Phplrt\Io\File;
use Railt\Container\ContainerInterface;
use Railt\Foundation\ApplicationInterface;
use Railt\Foundation\Config\Repository;
use Railt\Http\Factory;
use Railt\Http\ResponseInterface;
use Railt\SymfonyBundle\Config;
use Railt\SymfonyBundle\Exception\EndpointArgumentNotFoundException;
use Railt\SymfonyBundle\Exception\SchemaArgumentNotFoundException;
use Railt\SymfonyBundle\Http\SymfonyProvider;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Class GraphQLController
 */
class GraphQLController
{
    /**
     * @var ApplicationInterface
     */
    private $app;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var FileLocatorInterface
     */
    private $locator;

    /**
     * @var Stopwatch|null
     */
    private $stopwatch;

    /**
     * @var array
     */
    private $mergedEndpoints;

    /**
     * GraphQLController constructor.
     *
     * @param ContainerInterface $container
     * @param Configurator       $config
     */
    public function __construct(ApplicationInterface $app, Config $config, FileLocator $locator, ?Stopwatch $stopwatch)
    {
        $this->app = $app;
        $this->config = $config;
        $this->locator = $locator;
        $this->stopwatch = $stopwatch;

        $this->mergeConfig();
    }

    /**
     * @param Request $request
     * @throws \LogicException
     * @return mixed
     */
    public function handleAction(Request $request, ?string $endpoint)
    {
        if (!$endpoint) {
            throw new EndpointArgumentNotFoundException();
        }

        $response = $this->execute($request, $endpoint);

        $jsonResponse = new JsonResponse($response->render(), $response->getStatusCode(), [], true);
        $jsonResponse->setEncodingOptions($response->getJsonOptions());

        if ($this->config->isDebug()) {
            $jsonResponse->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        }

        return $jsonResponse;
    }

    private function execute(Request $request, $endpoint): ResponseInterface
    {
        return $this->trace('railt.init', function () use ($request, $endpoint) {
            $this->mergeConfig($endpoint);

            $schema = $this->config->getSchemaPath($endpoint);
            $endpoint = File::fromPathname($schema);
            $connection = $this->trace('railt.connect', fn () => $this->app->connect($endpoint));
            $factory = Factory::create(new SymfonyProvider($request));

            return $this->trace('railt.request', fn () => $connection->request($factory));
        });
    }

    private function trace(string $name, \Closure $closure)
    {
        if ($this->stopwatch) {
            $this->stopwatch->start($name);
        }

        $result = $closure();

        if ($this->stopwatch) {
            $this->stopwatch->stop($name);
        }

        return $result;
    }

    private function mergeConfig(string $endpoint = null)
    {
        if (isset($this->mergedEndpoints[$endpoint])) {
            return;
        }

        $this->app->get(Repository::class)->mergeWith($this->config->getConfigRepository($endpoint));
        $this->mergedEndpoints[$endpoint] = true;
    }

    ///**
    // * @param Request $request
    // * @return \Illuminate\Contracts\View\Factory|View
    // * @throws BindingResolutionException
    // */
    //public function playgroundAction(Request $request)
    //{
    //    return \view('railt::playground', [
    //        'endpoints' => $this->config->getEndpoints(),
    //        'route'     => $request->route(),
    //        'ui'        => $this->config->getPlayground(),
    //        'debug'     => $this->config->isDebug(),
    //    ]);
    //}
}
