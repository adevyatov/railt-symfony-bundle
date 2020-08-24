<?php

/**
 * This file is part of Railt package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Railt\SymfonyBundle\Exception;

/**
 * Class SchemaArgumentNotFoundException
 */
class SchemaArgumentNotFoundException extends \InvalidArgumentException
{
    private const MSG = 'You should pass "schema" argument to your controller';

    public function __construct($message = self::MSG, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
