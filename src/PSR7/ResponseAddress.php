<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use function sprintf;

class ResponseAddress extends OperationAddress
{
    /** @var int */
    protected $responseCode;

    public function __construct(string $path, string $method, int $responseCode)
    {
        parent::__construct($path, $method);
        $this->responseCode = $responseCode;
    }

    public function responseCode(): int
    {
        return $this->responseCode;
    }

    public function __toString(): string
    {
        return sprintf('Response [%s %s %d]', $this->method, $this->path, $this->responseCode);
    }
}
