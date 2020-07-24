<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception;

class InvalidServerRequestMessage extends ValidationFailed
{
    public static function fromOriginal(ValidationFailed $e) : self
    {
        return new static('Server Request message failed validation', 0, $e);
    }
}
