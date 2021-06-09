<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR15\Exception;

use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;

class InvalidServerRequestMessage extends ValidationFailed
{
    public static function because(ValidationFailed $e): self
    {
        return new self('Server Request message failed validation', 0, $e);
    }
}
