<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Exception;

use RuntimeException;
use Throwable;

final class InvalidSchema extends RuntimeException
{
    public static function becauseDefensiveSchemaValidationFailed(Throwable $e) : self
    {
        return new static('Schema validation failed: ' . $e->getMessage(), $e->getCode(), $e);
    }

    public static function becauseTypeIsNotKnown(string $type) : self
    {
        return new static("Type '%s' is unexpected.", $type);
    }
}
