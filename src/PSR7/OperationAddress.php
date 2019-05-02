<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7;


class OperationAddress
{
    /** @var string */
    protected $path;
    /** @var string */
    protected $method;

    /**
     * @param string $path
     * @param string $method
     */
    public function __construct(string $path, string $method)
    {
        $this->path   = $path;
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return $this->path;
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
        return new OperationAddress($this->path, $this->method);
    }
}