<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception;

class InvalidRequestMessage extends ValidationFailed
{
    public static function because(ValidationFailed $e) : self
    {
        return new static('Request message failed validation', 0, $e);
    }
}
