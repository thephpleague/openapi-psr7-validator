<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR15;

use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Psr\Http\Server\MiddlewareInterface;

class ValidationMiddlewareBuilder extends ValidatorBuilder
{
    /** @var bool */
    protected $shouldValidateResponse = true;

    /**
     * @return $this
     */
    public function shouldValidateResponse(bool $value): self
    {
        $this->shouldValidateResponse = $value;

        return $this;
    }

    public function getValidationMiddleware(): MiddlewareInterface
    {
        return new ValidationMiddleware(
            $this->getServerRequestValidator(),
            $this->shouldValidateResponse ? $this->getResponseValidator() : null
        );
    }
}
