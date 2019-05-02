<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception;


class NoMethod extends NoPath
{
    /** @var string */
    protected $method;

    static function fromPathAndMethod(string $path, string $method): self
    {
        $i       = new self(sprintf("OpenAPI spec contains no such operation [%s,%s]", $path, $method));
        $i->path   = $path;
        $i->method = $method;
        return $i;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }


}