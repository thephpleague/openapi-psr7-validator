<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

use InvalidArgumentException;
use function is_numeric;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function str_replace;
use function strtok;

// As seen in OpenApi spec (may include path parameters like /user/{id})
class PathAddress
{
    /** @var string */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function path() : string
    {
        return $this->path;
    }

    public function getPathAddress() : self
    {
        return new self($this->path);
    }

    /**
     * Parses given URL and returns params according to the pattern.
     *
     * Example:
     * $specPath = "/users/{id}";
     * $url = "/users/12";
     * returns ["id"=>12]
     *
     * @param string $specPath as seen in OpenAPI spec
     * @param string $url      as seen in actual HTTP Request/ServerRequest
     *
     * @return mixed[] return array of ["paramName"=>"parsedValue", ...]
     */
    public static function parseParams(string $specPath, string $url) : array
    {
        // pattern: /a/{b}/c/{d}
        // actual:  /a/12/c/some
        // result:  ['b'=>'12', 'd'=>'some']

        // 0. Filter URL, remove query string
        $url = strtok($url, '?');

        // 1. Find param names
        preg_match_all('#{([^}]+)}#', $specPath, $m);
        $parameterNames = $m[1];

        // 2. Parse param values
        $pattern = '#' . str_replace(['{', '}'], ['(?<', '>[^/]+)'], $specPath) . '#';

        if (! preg_match($pattern, $url, $matches)) {
            throw new InvalidArgumentException(sprintf("Unable to parse '%s' against the pattern '%s'", $url, $specPath));
        }

        // 3. Combine keys and values
        $parsedParams = [];
        foreach ($parameterNames as $name) {
            $value = $matches[$name];

            // cast numeric
            if (is_numeric($value)) {
                $value += 0; // that will cast it properly
            }

            $parsedParams[$name] = $value;
        }

        return $parsedParams;
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
}
