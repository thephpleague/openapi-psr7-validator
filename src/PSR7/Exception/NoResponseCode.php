<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception;


class NoResponseCode extends NoOperation
{
    /** @var int */
    protected $responseCode;

    static function fromPathAndMethodAndResponseCode(string $path, string $method, int $responseCode): self
    {
        $i               = new self(sprintf("OpenAPI spec contains no such operation [%s,%s,%d]", $path, $method, $responseCode));
        $i->path         = $path;
        $i->method       = $method;
        $i->responseCode = $responseCode;
        return $i;
    }

    /**
     * @return int
     */
    public function responseCode(): int
    {
        return $this->responseCode;
    }

}