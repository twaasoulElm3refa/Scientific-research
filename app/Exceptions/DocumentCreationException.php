<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

class DocumentCreationException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
