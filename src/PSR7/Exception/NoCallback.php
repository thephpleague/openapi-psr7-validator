<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception;

use function sprintf;

class NoCallback extends NoOperation
{
    /** @var string */
    protected $callbackName;

    /** @var string */
    protected $callbackMethod;

    public static function fromCallbackPath(string $path, string $method, string $callbackName, string $callbackMethod): self
    {
        $i                 = new self(sprintf('OpenAPI spec contains no such callback [%s,%s,%s,%s]', $path, $method, $callbackName, $callbackMethod));
        $i->path           = $path;
        $i->method         = $method;
        $i->callbackName   = $callbackName;
        $i->callbackMethod = $callbackMethod;

        return $i;
    }

    public function method(): string
    {
        return $this->method;
    }
}
