<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Exception;

use function sprintf;

// Indicates that data did not match a given content-type
final class ContentTypeMismatch extends SchemaMismatch
{
    public static function fromContentType(string $contentType, string $value): self
    {
        $exception       = new self(sprintf("Value '%s' does not match content-type %s", $value, $contentType));
        $exception->data = $value;

        return $exception;
    }
}
