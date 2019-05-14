<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Response as ResponseSpec;
use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\Exception\NoOperation;
use OpenAPIValidation\PSR7\Exception\NoPath;
use OpenAPIValidation\PSR7\Exception\NoResponseCode;
use Psr\Http\Message\ServerRequestInterface;

trait SpecFinder
{
    /**
     * Find a particular operation (path + method) in the spec
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
     * Find a particular path in the spec
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
     * Find the schema which describes a given response
     */
    protected function findResponseSpec(ResponseAddress $addr) : ResponseSpec
    {
        $operation = $this->findOperationSpec($addr->getOperationAddress());

        $response = $operation->responses->getResponse($addr->responseCode());
        if (! $response) {
            throw NoResponseCode::fromPathAndMethodAndResponseCode(
                $addr->path(),
                $addr->method(),
                $addr->responseCode()
            );
        }

        return $response;
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
