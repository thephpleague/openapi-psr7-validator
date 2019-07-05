<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR15;

use OpenAPIValidation\PSR7\ValidatorBuilder;
use Psr\Http\Server\MiddlewareInterface;

class ValidationMiddlewareBuilder extends ValidatorBuilder
{
    public function getValidationMiddleware() : MiddlewareInterface
    {
        return new ValidationMiddleware(
            $this->getServerRequestValidator(),
            $this->getResponseValidator()
        );
    }
}
