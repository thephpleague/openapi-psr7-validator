<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7;


class ResponseAddress extends OperationAddress
{
    /** @var int */
    protected $responseCode;

    /**
     * @param int $responseCode
     */
    public function __construct(string $path, string $method, int $responseCode)
    {
        parent::__construct($path, $method);
        $this->responseCode = $responseCode;
    }

    /**
     * @return int
     */
    public function responseCode(): int
    {
        return $this->responseCode;
    }
}