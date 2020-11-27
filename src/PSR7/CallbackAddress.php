<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use function sprintf;

class CallbackAddress extends OperationAddress
{
    /** @var string */
    protected $callbackName;

    /** @var string */
    protected $callbackMethod;

    public function __construct(string $path, string $method, string $callbackName, string $callbackMethod)
    {
        parent::__construct($path, $method);
        $this->callbackName   = $callbackName;
        $this->callbackMethod = $callbackMethod;
    }

    public function callbackName(): string
    {
        return $this->callbackName;
    }

    public function callbackMethod(): string
    {
        return $this->callbackMethod;
    }

    public function __toString(): string
    {
        return sprintf('Callback [%s %s %s %s]', $this->method, $this->path, $this->callbackName, $this->callbackMethod);
    }
}
