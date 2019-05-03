<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7;


use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Response as ResponseSpec;
use OpenAPIValidation\PSR7\Exception\NoOperation;
use OpenAPIValidation\PSR7\Exception\NoPath;
use OpenAPIValidation\PSR7\Exception\NoResponseCode;

abstract class Validator
{
    /** @var OpenApi */
    protected $openApi;

    /**
     * @param OpenApi $schema
     */
    public function __construct(OpenApi $schema)
    {
        $this->openApi = $schema;
    }

    /**
     * Find the schema which describes a given response
     *
     * @param ResponseAddress $addr
     * @return ResponseSpec
     */
    protected function findResponseSpec(ResponseAddress $addr): ResponseSpec
    {
        $operation = $this->findOperationSpec($addr->getOperationAddress());

        $response = $operation->responses->getResponse($addr->responseCode());
        if (!$response) {
            throw NoResponseCode::fromPathAndMethodAndResponseCode($addr->path(), $addr->method(), $addr->responseCode());
        }

        return $response;
    }

    /**
     * Find a particualr operation (path + method) in the spec
     *
     * @param OperationAddress $addr
     * @return Operation
     */
    protected function findOperationSpec(OperationAddress $addr): Operation
    {
        $pathSpec = $this->findPathSpec($addr);

        if (!isset($pathSpec->getOperations()[$addr->method()])) {
            throw NoOperation::fromPathAndMethod($addr->path(), $addr->method());
        }
        return $pathSpec->getOperations()[$addr->method()];
    }

    /**
     * Find a particualr path in the spec
     *
     * @param OperationAddress $addr
     * @return Operation
     */
    protected function findPathSpec(OperationAddress $addr): PathItem
    {
        $pathSpec = $this->openApi->paths->getPath($addr->path());

        if (!$pathSpec) {
            throw NoPath::fromPath($addr->path());
        }

        return $pathSpec;
    }

    /**
     * Check the openapi spec and find matching operations(path+method)
     * This should consider path parameters as well
     * "/users/12" should match both ["/users/{id}", "/users/{group}"]
     *
     * @param string $path like "/users/12"
     * @param string $method like "post"
     * @return OperationAddress[]
     */
    protected function findMatchingOperations(string $path, string $method): array
    {
        $matchedOperations = [];

        foreach ($this->openApi->paths as $specPath => $pathItemSpec) {
            if (!PathAddress::isPathMatchesSpec($specPath, $path)) {
                continue;
            }

            foreach ($pathItemSpec->getOperations() as $opMethod => $operation) {
                if ($opMethod != $method) {
                    continue;
                }

                // ok looks like method and path matched
                $matchedOperations[] = new OperationAddress($specPath, $opMethod);
            }
        }

        return $matchedOperations;
    }

}