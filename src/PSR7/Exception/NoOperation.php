<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception;

use function sprintf;

class NoOperation extends NoPath
{
    /** @var string */
    protected $method;

    public static function fromPathAndMethod(string $path, string $method): self
    {
        $i         = new self(sprintf('OpenAPI spec contains no such operation [%s,%s]', $path, $method));
        $i->path   = $path;
        $i->method = $method;

        return $i;
    }

    public function method(): string
    {
        return $this->method;
    }
}
