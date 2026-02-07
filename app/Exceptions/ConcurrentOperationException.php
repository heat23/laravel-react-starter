<?php

namespace App\Exceptions;

use RuntimeException;

class ConcurrentOperationException extends RuntimeException
{
    public function __construct(string $message = 'Another operation is already in progress. Please try again.')
    {
        parent::__construct($message);
    }
}
