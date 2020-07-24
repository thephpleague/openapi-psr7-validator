<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception;

use Exception;
use Throwable;

class InvalidResponseMessage extends Exception
{
    public static function fromOriginal(Throwable $e) : self
    {
        return new static('Response message failed validation', 0, $e);
    }
}
