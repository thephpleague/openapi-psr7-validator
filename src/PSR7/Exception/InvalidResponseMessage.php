<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception;

class InvalidResponseMessage extends ValidationFailed
{
    public static function because(ValidationFailed $e) : self
    {
        return new static('Response message failed validation', 0, $e);
    }
}
