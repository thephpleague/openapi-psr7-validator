<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception;

use function sprintf;

class NoPath extends ValidationFailed
{
    /** @var string */
    protected $path;

    public static function fromPath(string $path): self
    {
        $i       = new self(sprintf('OpenAPI spec contains no such operation [%s]', $path));
        $i->path = $path;

        return $i;
    }

    public function path(): string
    {
        return $this->path;
    }
}
