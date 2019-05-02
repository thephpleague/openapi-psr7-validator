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
use OpenAPIValidation\PSR7\Exception\MissedRequestHeader;
use OpenAPIValidation\PSR7\Exception\MissedResponseHeader;
use OpenAPIValidation\PSR7\Exception\NoContentType;
use OpenAPIValidation\PSR7\Exception\NoMethod;
use OpenAPIValidation\PSR7\Exception\NoPath;
use OpenAPIValidation\PSR7\Exception\NoResponseCode;
use OpenAPIValidation\PSR7\Exception\RequestHeadersMismatch;
use OpenAPIValidation\PSR7\Exception\ResponseBodyMismatch;
use OpenAPIValidation\PSR7\Exception\ResponseHeadersMismatch;
use OpenAPIValidation\PSR7\Exception\UnexpectedRequestHeader;
use OpenAPIValidation\PSR7\Exception\UnexpectedResponseContentType;
use OpenAPIValidation\PSR7\Exception\UnexpectedResponseHeader;
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
        try {
            $this->validateHeaders($response, $spec->headers);
        } catch (\Throwable $e) {
            switch ($e->getCode()) {
                case 200:
                    throw UnexpectedResponseHeader::fromResponseAddr($e->getMessage(), $addr);
                    break;
                case 201:
                    throw MissedResponseHeader::fromResponseAddr($e->getMessage(), $addr);
                    break;
                default:
                    throw ResponseHeadersMismatch::fromAddrAndCauseException($addr, $e);
            }
        }

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
    private function findOperationSpec(OperationAddress $addr): Operation
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
        $messageHeaders = $message->getHeaders();

        foreach ($messageHeaders as $header => $headerValues) {

            if (!array_key_exists($header, $headerSpecs)) {
                // By default this will not report unexpected headers (soft validation)
                // TODO, maybe this can be enabled later and controlled by custom options
                #throw new \RuntimeException($header, 200);
                continue;
            }

            foreach ($headerValues as $headerValue) {
                $validator = new SchemaValidator($headerSpecs[$header]->schema, $headerValue, $this->detectValidationStrategy($message));
                $validator->validate();
            }
        }

        // Check if message misses headers
        foreach ($headerSpecs as $header => $spec) {
            if (!$message->hasHeader($header)) {
                throw new \RuntimeException($header, 201);
            }
        }
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
     * @param RequestInterface $request
     */
    public function validateRequest(RequestInterface $request): void
    {
        // TODO, should this be implemented?
    }

    /**
     * @param OperationAddress $addr
     * @param ServerRequestInterface $serverRequest
     * @throws \cebe\openapi\exceptions\TypeErrorException
     */
    public function validateServerRequest(OperationAddress $addr, ServerRequestInterface $serverRequest): void
    {
        // 0. Find appropriate schema to validate against
        $spec = $this->findOperationSpec($addr);

        // 1. Validate Headers
        // An API call may require that custom headers be sent with an HTTP request. OpenAPI lets you define custom request headers as in: header parameters.
        $headerSpecs = [];
        foreach ($spec->parameters as $p) {
            if ($p->in != "header") {
                continue;
            }

            $headerData = json_decode(json_encode($p->getSerializableData()), true);
            unset($headerData['in']);
            unset($headerData['name']);
            $headerSpecs[$p->name] = new HeaderSpec($headerData);
        }

        try {
            $this->validateHeaders($serverRequest, $headerSpecs);
        } catch (\Throwable $e) {
            switch ($e->getCode()) {
                case 200:
                    throw UnexpectedRequestHeader::fromOperationAddr($e->getMessage(), $addr);
                    break;
                case 201:
                    throw MissedRequestHeader::fromOperationAddr($e->getMessage(), $addr);
                    break;
                default:
                    throw RequestHeadersMismatch::fromAddrAndCauseException($addr, $e);
            }
        }

        // 2. Validate Body

    }
}