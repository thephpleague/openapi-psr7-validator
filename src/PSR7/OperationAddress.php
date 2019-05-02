<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7;


class OperationAddress extends PathAddress
{
    /** @var string */
    protected $method;

    /**
     * @param string $path
     * @param string $method
     */
    public function __construct(string $path, string $method)
    {
        parent::__construct($path);
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }


    public function getOperationAddress(): self
    {
        return new self($this->path, $this->method);
    }
}