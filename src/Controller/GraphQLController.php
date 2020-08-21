<?php

/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\SymfonyBundle\Controller;

use Railt\Container\ContainerInterface;
use Railt\Foundation\ApplicationInterface;
use Railt\Http\Factory;
use Railt\Http\ResponseInterface;
use Railt\SymfonyBundle\Config;
use Railt\SymfonyBundle\Http\SymfonyProvider;
use Railt\SymfonyBundle\Storage\PSR6StorageBridge;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Config\FileLocator;

/**
 * Class GraphQLController
 */
class GraphQLController
{
    private const DEFAULT_SCHEMA = __DIR__ . '/../Resources/graphql/schema.graphqls';

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
     * GraphQLController constructor.
     *
     * @param ContainerInterface $container
     * @param Configurator       $config
     */
    public function __construct(ApplicationInterface $app, Config $config, FileLocator $locator)
    {
        $this->app = $app;
        $this->config = $config;
        $this->locator = $locator;

        if (! $config->isDebug()) {
            $app->alias(PSR6StorageBridge::class, Storage::class);
        }
    }

    /**
     * @param Request $request
     * @throws \LogicException
     * @return mixed
     */
    public function handleAction(Request $request, ?string $schema)
    {
        $schema = $schema ?? self::DEFAULT_SCHEMA;
        $response = $this->execute($request, $schema);

        $jsonResponse = new JsonResponse($response->render(), $response->getStatusCode(), [], true);
        $jsonResponse->setEncodingOptions($response->getJsonOptions());

        return $jsonResponse;
    }

    private function execute(Request $request, $schema): ResponseInterface
    {
        $schema = \Phplrt\Io\File::fromPathname($schema);

        $connection = $this->app->connect($schema);
        $factory = Factory::create(new SymfonyProvider($request));

        return $connection->request($factory);
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
