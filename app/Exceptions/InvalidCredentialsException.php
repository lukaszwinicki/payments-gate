<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidCredentialsException extends RuntimeException
{
    public function __construct(string $message = 'Invalid credentials.')
    {
        parent::__construct($message);
    }
}