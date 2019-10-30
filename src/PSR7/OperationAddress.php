<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidPath;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function str_replace;
use function strtok;

class OperationAddress
{
    /** @var string */
    protected $method;
    /** @var string */
    protected $path;

    public function __construct(string $path, string $method)
    {
        $this->path   = $path;
        $this->method = $method;
    }

    /**
     * Checks if path matches a specification
     *
     * @param string $specPath like "/users/{id}"
     * @param string $path     like "/users/12"
     */
    public static function isPathMatchesSpec(string $specPath, string $path) : bool
    {
        $pattern = '#^' . preg_replace('#{[^}]+}#', '[^/]+', $specPath) . '/?$#';

        return (bool) preg_match($pattern, $path);
    }

    public function method() : string
    {
        return $this->method;
    }

    public function __toString() : string
    {
        return sprintf('Request [%s %s]', $this->method, $this->path);
    }

    public function path() : string
    {
        return $this->path;
    }

    /**
     * Parses given URL and returns params according to the pattern.
     *
     * Example:
     * $specPath = "/users/{id}";
     * $url = "/users/12";
     * returns ["id"=>12]
     *
     * @param string $url as seen in actual HTTP Request/ServerRequest
     *
     * @return mixed[] return array of ["paramName"=>"parsedValue", ...]
     *
     * @throws InvalidPath
     */
    public function parseParams(string $url) : array
    {
        // pattern: /a/{b}/c/{d}
        // actual:  /a/12/c/some
        // result:  ['b'=>'12', 'd'=>'some']

        // 0. Filter URL, remove query string
        $url = strtok($url, '?');

        // 1. Find param names
        preg_match_all('#{([^}]+)}#', $this->path(), $m);
        $parameterNames = $m[1];

        // 2. Parse param values
        $pattern = '#' . str_replace(['{', '}'], ['(?<', '>[^/]+)'], $this->path()) . '#';

        if (! preg_match($pattern, $url, $matches)) {
            throw InvalidPath::becausePathDoesNotMatchPattern($url, $this);
        }

        // 3. Combine keys and values
        $parsedParams = [];
        foreach ($parameterNames as $name) {
            $parsedParams[$name] = $matches[$name];
        }

        return $parsedParams;
    }
}
