<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use function sprintf;

class CallbackResponseAddress extends CallbackAddress
{
    /** @var int */
    protected $responseCode;

    public function __construct(string $path, string $method, string $callbackName, string $callbackMethod, int $responseCode)
    {
        parent::__construct($path, $method, $callbackName, $callbackMethod);
        $this->responseCode = $responseCode;
    }

    public function responseCode(): int
    {
        return $this->responseCode;
    }

    public function __toString(): string
    {
        return sprintf('Callback [%s %s %s %s %d]', $this->method, $this->path, $this->callbackName, $this->callbackMethod, $this->responseCode);
    }
}
