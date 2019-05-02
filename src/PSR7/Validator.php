<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7;


use cebe\openapi\spec\Header as HeaderSpec;
use cebe\openapi\spec\MediaType as MediaTypeSpec;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Response as ResponseSpec;
use OpenAPIValidation\PSR7\Exception\NoContentType;
use OpenAPIValidation\PSR7\Exception\NoMethod;
use OpenAPIValidation\PSR7\Exception\NoPath;
use OpenAPIValidation\PSR7\Exception\NoResponseCode;
use OpenAPIValidation\Schema\Validator as SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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
        $pathSpec = $this->openApi->paths->getPath($addr->path());

        if (!$pathSpec) {
            throw NoPath::fromPath($addr->path());
        }

        if (!isset($pathSpec->getOperations()[$addr->method()])) {
            throw NoMethod::fromPathAndMethod($addr->path(), $addr->method());
        }
        return $pathSpec->getOperations()[$addr->method()];
    }



}