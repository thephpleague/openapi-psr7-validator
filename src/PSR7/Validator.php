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
use OpenAPIValidation\PSR7\Exception\ResponseBodyMismatch;
use OpenAPIValidation\PSR7\Exception\UnexpectedResponseContentType;
use OpenAPIValidation\Schema\Validator as SchemaValidator;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Validator
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
     * @param ResponseAddress $addr
     * @param ResponseInterface $response
     * @throws \Exception
     */
    public function validateResponse(ResponseAddress $addr, ResponseInterface $response): void
    {
        // 0. Find appropriate schema to validate against
        $spec = $this->findResponseSpec($addr);

        // 1. Validate Headers
        $this->validateHeaders($response, $spec->headers);

        // 2. Validate Body
        try {
            $this->validateBody($response, $spec->content);
        } catch (\Throwable $e) {
            switch ($e->getCode()) {
                case 100:
                    throw UnexpectedResponseContentType::fromResponseAddr($e->getMessage(), $addr);
                default:
                    throw ResponseBodyMismatch::fromAddrAndCauseException($addr, $e);
            }
        }
    }

    /**
     * Find the schema which describes a given response
     *
     * @param ResponseAddress $addr
     * @return ResponseSpec
     */
    private function findResponseSpec(ResponseAddress $addr): ResponseSpec
    {
        $operation = $this->findOperation($addr->getOperationAddress());

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
    private function findOperation(OperationAddress $addr): Operation
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

    /**
     * @param MessageInterface $message
     * @param HeaderSpec[] $headerSpecs
     */
    protected function validateHeaders(MessageInterface $message, array $headerSpecs): void
    {
        #var_dump($headerSpecs);
    }

    /**
     * @param MessageInterface $message
     * @param MediaTypeSpec[] $mediaTypeSpecs
     * @throws \Exception
     */
    protected function validateBody(MessageInterface $message, array $mediaTypeSpecs): void
    {
        $contentTypes = $message->getHeader('Content-Type');
        if (!$contentTypes) {
            throw new NoContentType();
        }
        $contentType = $contentTypes[0]; # use the first value

        // does the response contain one of described media types?
        if (!isset($mediaTypeSpecs[$contentType])) {
            throw new \RuntimeException($contentType, 100);
        }

        // ok looks good, now apply validation
        $body = (string)$message->getBody();
        if (preg_match("#^application/json#", $contentType)) {
            $body = json_decode($body, true);
            if (json_last_error()) {
                throw new \RuntimeException("Unable to decode JSON body content: " . json_last_error_msg());
            }
        }
        $validator = new SchemaValidator($mediaTypeSpecs[$contentType]->schema, $body, $this->detectValidationStrategy($message));
        $validator->validate();
    }

    /**
     * Distinguish requests and responses, so we can treat them differently (writeOnly/readOnly OAS keywords)
     *
     * @param MessageInterface $message
     * @return int
     */
    private function detectValidationStrategy(MessageInterface $message): int
    {
        if ($message instanceof ResponseInterface) {
            return \OpenAPIValidation\Schema\Validator::VALIDATE_AS_RESPONSE;
        } else {
            return \OpenAPIValidation\Schema\Validator::VALIDATE_AS_REQUEST;
        }
    }

    /**
     * @param RequestInterface $request
     */
    public function validateRequest(RequestInterface $request): void
    {

    }

    /**
     * @param ServerRequestInterface $serverRequest
     */
    public function validateServerRequest(ServerRequestInterface $serverRequest): void
    {

    }
}