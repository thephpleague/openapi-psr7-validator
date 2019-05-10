<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

class ResponseAddress extends OperationAddress
{
    /** @var int */
    protected $responseCode;

    public function __construct(string $path, string $method, int $responseCode)
    {
        parent::__construct($path, $method);
        $this->responseCode = $responseCode;
    }

    public function responseCode() : int
    {
        return $this->responseCode;
    }
}
