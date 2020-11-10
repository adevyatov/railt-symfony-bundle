<?php

declare(strict_types=1);

namespace Railt\SymfonyBundle\Exception;

class EndpointDefinitionNotFoundException extends \InvalidArgumentException
{
    private const MSG = 'Endpoint "%s" not found in Railt configuration. Available endpoints: [%s]';

    public function __construct(string $required, array $allowed)
    {
        $message = \sprintf(self::MSG, $required, \implode(',', $allowed));
        parent::__construct($message);
    }
}
