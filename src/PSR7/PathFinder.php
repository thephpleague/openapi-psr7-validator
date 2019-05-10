<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 07 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Server;
use Psr\Http\Message\UriInterface;

// This class finds operations matching the given URI+method
// That would be a very simple operation if there were no "Servers" keyword.
// We need to take into account possible base-url case (and its templating feature)
// @see https://swagger.io/docs/specification/api-host-and-base-path/
class PathFinder
{
    /** @var OpenApi */
    protected $openApiSpec;
    /** @var UriInterface */
    protected $uri;
    /** @var string $method like "get" */
    protected $method;

    /**
     * @param OpenApi $openApiSpec
     * @param UriInterface $uri
     * @param string $method
     */
    public function __construct(OpenApi $openApiSpec, UriInterface $uri, string $method)
    {
        $this->openApiSpec = $openApiSpec;
        $this->uri         = $uri;
        $this->method      = strtolower($method);
    }

    /**
     * Make search
     *
     * @return OperationAddress[]
     */
    public function search(): array
    {
        $paths = [];

        // 1. Find operations which match criteria
        $opCandidates = $this->searchForCandidates();

        // 2. for each operation, find suitable "servers" (with respect to overriding)
        foreach ($opCandidates as $i => $opAddress) {
            $opCandidates[$i] = [
                'addr'    => $opAddress,
                'servers' => $this->findServersForOperation($opAddress),
            ];
        }

        // 3. Check each candidate operation against it's servers
        foreach ($opCandidates as $opCandidate) {
            /** @var Server $server */
            foreach ($opCandidate['servers'] as $server) {
                $fullSpecPath = rtrim($server->url, "/") . "/" . ltrim($opCandidate['addr']->path(), '/');
                // 3.1 Compare this path against the real/given path
                $uriWithNoQUeryString = strtok((string)$this->uri, "?");
                if (PathAddress::isPathMatchesSpec($fullSpecPath, $uriWithNoQUeryString)) {
                    // path matched!
                    $paths[] = $opCandidate['addr'];
                }
            }
        }

        return $paths;
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
            # 1. path ends with the same given path (so there can be some prefixes in servers)
            # like
            # $this->path: /v1/users/admin
            # specPath:       /users/{group}
            # servers:     /v1
            $pattern = "#" . preg_replace("#{[^}]+}#", "[^/]+", $specPath) . "/?$#";

            if (!(bool)preg_match($pattern, $this->uri->getPath())) {
                continue;
            }

            # 2. method matches
            foreach ($pathItemSpec->getOperations() as $opMethod => $operation) {
                if ($opMethod != $this->method) {
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
     * @param OperationAddress $opAddress
     * @return Server[]
     */
    private function findServersForOperation(OperationAddress $opAddress): array
    {
        $path      = $this->openApiSpec->paths->getPath($opAddress->path());
        $operation = $path->getOperations()[$opAddress->method()];

        // 1. Check servers on operation level
        if (array_key_exists('servers', (array)$operation->getSerializableData())) {
            return $operation->servers;
        } elseif (array_key_exists('servers', (array)$path->getSerializableData())) {
            return $path->servers;
        } elseif (array_key_exists('servers', (array)$this->openApiSpec->getSerializableData())) {
            return $this->openApiSpec->servers;
        } else {
            # fallback
            return [new Server(['url' => '/'])];
        }

    }

}