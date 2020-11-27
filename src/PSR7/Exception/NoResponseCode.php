<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception;

use function sprintf;

class NoResponseCode extends NoOperation
{
    /** @var int */
    protected $responseCode;

    public static function fromPathAndMethodAndResponseCode(string $path, string $method, int $responseCode): self
    {
        $i               = new self(sprintf('OpenAPI spec contains no such operation [%s,%s,%d]', $path, $method, $responseCode));
        $i->path         = $path;
        $i->method       = $method;
        $i->responseCode = $responseCode;

        return $i;
    }

    public function responseCode(): int
    {
        return $this->responseCode;
    }
}
