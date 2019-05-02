<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception;


class NoPath extends \RuntimeException
{
    /** @var string */
    protected $path;

    static function fromPath(string $path): self
    {
        $i       = new self(sprintf("OpenAPI spec contains no such operation [%s]", $path));
        $i->path = $path;
        return $i;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }


}