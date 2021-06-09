<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidPath;
use League\OpenAPIValidation\Schema\Exception\InvalidSchema;

use function implode;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function preg_split;
use function sprintf;
use function strtok;

use const PREG_SPLIT_DELIM_CAPTURE;

class OperationAddress
{
    private const PATH_PLACEHOLDER = '#{[^}]+}#';

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
    public static function isPathMatchesSpec(string $specPath, string $path): bool
    {
        $pattern = '#^' . preg_replace(self::PATH_PLACEHOLDER, '[^/]+', $specPath) . '/?$#';

        return (bool) preg_match($pattern, $path);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function __toString(): string
    {
        return sprintf('Request [%s %s]', $this->method, $this->path);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function hasPlaceholders(): bool
    {
        return preg_match(self::PATH_PLACEHOLDER, $this->path()) === 1;
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
    public function parseParams(string $url): array
    {
        // pattern: /a/{b}/c/{d}
        // actual:  /a/12/c/some
        // result:  ['b'=>'12', 'd'=>'some']

        // 0. Filter URL, remove query string
        $url = strtok($url, '?');

        // 1. Find param names and build pattern
        $pattern = $this->buildPattern($this->path(), $parameterNames);

        // 2. Parse param values
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

    /**
     * It builds PCRE pattern, which can be used to parse path. It also extract parameter names
     *
     * @param array<string>|null $parameterNames
     */
    protected function buildPattern(string $url, ?array &$parameterNames): string
    {
        $parameterNames = [];
        $pregParts      = [];
        $inParameter    = false;

        $parts = preg_split('#([{}])#', $url, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $part) {
            switch ($part) {
                case '{':
                    if ($inParameter) {
                        throw InvalidSchema::becauseBracesAreNotBalanced($url);
                    }

                    $inParameter = true;
                    continue 2;
                case '}':
                    if (! $inParameter) {
                        throw InvalidSchema::becauseBracesAreNotBalanced($url);
                    }

                    $inParameter = false;
                    continue 2;
            }

            if ($inParameter) {
                $pregParts[]      = '(?<' . $part . '>[^/]+)';
                $parameterNames[] = $part;
            } else {
                $pregParts[] = preg_quote($part, '#');
            }
        }

        return '#' . implode($pregParts) . '#';
    }
}
