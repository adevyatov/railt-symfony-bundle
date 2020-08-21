<?php

declare(strict_types=1);

namespace Railt\SymfonyBundle\Http;

use Railt\Http\Provider\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class SymfonyProvider implements ProviderInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * LaravelProvider constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(\is_array($data) ? $data : []);
        }

        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getQueryArguments(): array
    {
        return $this->request->query->all();
    }

    /**
     * @return array
     */
    public function getPostArguments(): array
    {
        return $this->request->request->all();
    }

    /**
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->request->getContentType();
    }

    /**
     * @return string
     * @throws \LogicException
     */
    public function getBody(): string
    {
        return (string)$this->request->getContent(false);
    }
}
