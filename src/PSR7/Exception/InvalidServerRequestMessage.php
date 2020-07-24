<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception;

use Exception;
use Throwable;

class InvalidServerRequestMessage extends Exception
{
    public static function fromOriginal(Throwable $e) : self
    {
        return new static('Server Request message failed validation', 0, $e);
    }
}
