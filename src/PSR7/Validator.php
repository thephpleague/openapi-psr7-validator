<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

use cebe\openapi\Reader;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Response as ResponseSpec;
use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\Foundation\CachingProxy;
use OpenAPIValidation\PSR7\Exception\NoOperation;
use OpenAPIValidation\PSR7\Exception\NoPath;
use OpenAPIValidation\PSR7\Exception\NoResponseCode;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use function crc32;
use function realpath;

abstract class Validator
{
    /** @var OpenApi */
    protected $openApi;

    protected function __construct(OpenApi $schema)
    {
        $this->openApi = $schema;
    }

    public static function fromYaml(string $yaml, ?CacheItemPoolInterface $cache = null) : self
    {
        $oas = CachingProxy::cachedRead($cache, 'openapi_' . crc32($yaml), static function () use ($yaml) {
            return Reader::readFromYaml($yaml);
        });

        $oas->resolveReferences(new ReferenceContext($oas, '/'));

        return new static($oas);
    }

    public static function fromJson(string $json, ?CacheItemPoolInterface $cache = null) : self
    {
        $oas = CachingProxy::cachedRead($cache, 'openapi_' . crc32($json), static function () use ($json) {
            return Reader::readFromJson($json);
        });

        $oas->resolveReferences(new ReferenceContext($oas, '/'));

        return new static($oas);
    }

    public static function fromYamlFile(string $yamlFile, ?CacheItemPoolInterface $cache = null) : self
    {
        \Respect\Validation\Validator::file()->assert($yamlFile);

        $oas = CachingProxy::cachedRead($cache, 'openapi_' . crc32(realpath($yamlFile)), static function () use ($yamlFile) {
            return Reader::readFromYamlFile($yamlFile);
        });

        $oas->resolveReferences(new ReferenceContext($oas, realpath($yamlFile)));

        return new static($oas);
    }

    public static function fromJsonFile(string $jsonFile, ?CacheItemPoolInterface $cache = null) : self
    {
        \Respect\Validation\Validator::file()->assert($jsonFile);

        $oas = CachingProxy::cachedRead($cache, 'openapi_' . crc32(realpath($jsonFile)), static function () use ($jsonFile) {
            return Reader::readFromJsonFile($jsonFile);
        });

        $oas->resolveReferences(new ReferenceContext($oas, realpath($jsonFile)));

        return new static($oas);
    }

    /**
     * Find the schema which describes a given response
     */
    protected function findResponseSpec(ResponseAddress $addr) : ResponseSpec
    {
        $operation = $this->findOperationSpec($addr->getOperationAddress());

        $response = $operation->responses->getResponse($addr->responseCode());
        if (! $response) {
            throw NoResponseCode::fromPathAndMethodAndResponseCode($addr->path(), $addr->method(), $addr->responseCode());
        }

        return $response;
    }

    /**
     * Find a particualr operation (path + method) in the spec
     */
    protected function findOperationSpec(OperationAddress $addr) : Operation
    {
        $pathSpec = $this->findPathSpec($addr);

        if (! isset($pathSpec->getOperations()[$addr->method()])) {
            throw NoOperation::fromPathAndMethod($addr->path(), $addr->method());
        }

        return $pathSpec->getOperations()[$addr->method()];
    }

    /**
     * Find a particualr path in the spec
     */
    protected function findPathSpec(PathAddress $addr) : PathItem
    {
        $pathSpec = $this->openApi->paths->getPath($addr->path());

        if (! $pathSpec) {
            throw NoPath::fromPath($addr->path());
        }

        return $pathSpec;
    }

    /**
     * Check the openapi spec and find matching operations(path+method)
     * This should consider path parameters as well
     * "/users/12" should match both ["/users/{id}", "/users/{group}"]
     *
     * @param ServerRequest $request
     *
     * @return OperationAddress[]
     */
    protected function findMatchingOperations(ServerRequestInterface $request) : array
    {
        $pathFinder = new PathFinder($this->openApi, $request->getUri(), $request->getMethod());

        return $pathFinder->search();
    }
}
