<?php
declare(strict_types=1);

namespace toubilib\api\security;

use RuntimeException;
use Throwable;

class InvalidTokenException extends RuntimeException
{
    public function __construct(string $message = 'Jeton invalide.', ?Throwable $previous = null)
    {
        parent::__construct($message, previous: $previous);
    }
}
