<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

class OperationAddress extends PathAddress
{
    /** @var string */
    protected $method;

    public function __construct(string $path, string $method)
    {
        parent::__construct($path);
        $this->method = $method;
    }

    public function method() : string
    {
        return $this->method;
    }

    public function getOperationAddress() : self
    {
        return new self($this->path, $this->method);
    }
}
