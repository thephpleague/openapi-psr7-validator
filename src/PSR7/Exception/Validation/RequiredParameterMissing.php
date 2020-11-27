<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception\Validation;

use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;

class RequiredParameterMissing extends ValidationFailed
{
    /** @var string */
    protected $name;

    public static function fromName(string $name): self
    {
        $exception       = new self();
        $exception->name = $name;

        return $exception;
    }

    public function name(): string
    {
        return $this->name;
    }
}
