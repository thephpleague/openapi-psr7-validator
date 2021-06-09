<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Server;

use function count;
use function ltrim;
use function parse_url;
use function preg_match;
use function preg_replace;
use function rtrim;
use function sprintf;
use function strtolower;
use function usort;

use const PHP_URL_PATH;

// This class finds operations matching the given URI+method
// That would be a very simple operation if there were no "Servers" keyword.
// We need to take into account possible base-url case (and its templating feature)
// @see https://swagger.io/docs/specification/api-host-and-base-path/
//
// More: as discussed here https://github.com/lezhnev74/openapi-psr7-validator/issues/32
// "schema://hostname" should not be included in the validation process (assume any hostname matches)
class PathFinder
{
    /** @var OpenApi */
    protected $openApiSpec;
    /** @var string */
    protected $path;
    /** @var string $method like "get" */
    protected $method;
    /** @var OperationAddress[] */
    protected $searchResult;

    public function __construct(OpenApi $openApiSpec, string $uri, string $method)
    {
        $this->openApiSpec = $openApiSpec;
        $this->path        = (string) parse_url($uri, PHP_URL_PATH);
        $this->method      = strtolower($method);
    }

    /**
     * Determine matching paths.
     *
     * @return PathItem[]
     */
    public function getPathMatches(): array
    {
        // Determine if path matches exactly.
        $match = $this->openApiSpec->paths->getPath($this->path);
        if ($match !== null) {
            return [$match];
        }

        // Probably path is parametrized or matches partially. Determine candidates and try to match path.
        $matches = [];
        foreach ($this->search() as $result) {
            $matches[] = $this->openApiSpec->paths->getPath($result->path());
        }

        return $matches;
    }

    /**
     * @return OperationAddress[]
     */
    public function search(): array
    {
        if ($this->searchResult === null) {
            $this->searchResult = $this->doSearch();
        }

        return $this->searchResult;
    }

    /**
     * @return OperationAddress[]
     */
    private function doSearch(): array
    {
        $paths = [];

        // 1. Find operations which match criteria
        $opCandidates = $this->searchForCandidates();

        // 2. for each operation, find suitable "servers" (with respect to overriding)
        foreach ($opCandidates as $i => $opAddress) {
            $opCandidates[$i] = [
                'addr' => $opAddress,
                'servers' => $this->findServersForOperation($opAddress),
            ];
        }

        // 3. Check each candidate operation against it's servers
        foreach ($opCandidates as $opCandidate) {
            /** @var Server $server */
            foreach ($opCandidate['servers'] as $server) {
                $candidatePath = $this->composeFullOperationPath($server, $opCandidate['addr']);

                // 3.1 Compare this path against the real/given path
                if (! OperationAddress::isPathMatchesSpec($candidatePath, $this->path)) {
                    continue;
                }

                // path matched!
                $paths[] = $opCandidate['addr'];
                break;
            }
        }

        return $this->prioritizeStaticPaths($paths);
    }

    /**
     * Find operations which in general match the request:
     * 1. path ends with the same given path (so there can be some prefixes in servers)
     * 2. method matches
     *
     * @return OperationAddress[]
     */
    private function searchForCandidates(): array
    {
        $matchedOperations = [];

        foreach ($this->openApiSpec->paths as $specPath => $pathItemSpec) {
            // 1. path ends with the same given path (so there can be some prefixes in servers)
            // like
            // $this->path: /v1/users/admin
            // specPath:       /users/{group}
            // servers:     /v1
            $pattern = '#' . preg_replace('#{[^}]+}#', '[^/]+', $specPath) . '/?$#';

            if (! (bool) preg_match($pattern, $this->path)) {
                continue;
            }

            // 2. method matches
            foreach ($pathItemSpec->getOperations() as $opMethod => $operation) {
                if ($opMethod !== $this->method) {
                    continue;
                }

                // ok looks like method and path matched
                $matchedOperations[] = new OperationAddress($specPath, $opMethod);
            }
        }

        return $matchedOperations;
    }

    /**
     * The global servers array can be overridden on the path level or operation level.
     *
     * @return Server[]
     */
    private function findServersForOperation(OperationAddress $opAddress): array
    {
        $path      = $this->openApiSpec->paths->getPath($opAddress->path());
        $operation = $path->getOperations()[$opAddress->method()];

        // 1. Check servers on operation level
        if (isset($operation->servers) && count($operation->servers) > 0) {
            return $operation->servers;
        }

        // 2. Check servers on path level
        if (isset($path->servers) && count($path->servers) > 0) {
            return $path->servers;
        }

        // 3. Fallback with servers on root level
        return $this->openApiSpec->servers;
    }

    private function composeFullOperationPath(Server $server, OperationAddress $addr): string
    {
        return sprintf(
            '%s/%s',
            rtrim((string) parse_url($server->url, PHP_URL_PATH), '/'),
            ltrim($addr->path(), '/')
        );
    }

    /**
     * @param OperationAddress[] $paths
     *
     * @return OperationAddress[]
     */
    private function prioritizeStaticPaths(array $paths): array
    {
        usort($paths, static function (OperationAddress $a, OperationAddress $b): int {
            if ($a->hasPlaceholders() && ! $b->hasPlaceholders()) {
                return 1;
            }

            if ($b->hasPlaceholders() && ! $a->hasPlaceholders()) {
                return -1;
            }

            return 0;
        });

        return $paths;
    }
}
