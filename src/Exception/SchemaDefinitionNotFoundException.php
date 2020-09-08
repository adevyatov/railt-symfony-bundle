<?php

declare(strict_types=1);

namespace Railt\SymfonyBundle\Exception;

class SchemaDefinitionNotFoundException extends \InvalidArgumentException
{
    private const MSG = 'Schema "%s" not found in Railt configuration. Available schemas: [%s]';

    public function __construct(string $required, array $allowed)
    {
        $message = \sprintf(self::MSG, $required, \implode(',', $allowed));
        parent::__construct($message);
    }
}
