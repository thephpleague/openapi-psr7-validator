<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR15;

use League\OpenAPIValidation\PSR15\Exception\InvalidResponseMessage;
use League\OpenAPIValidation\PSR15\Exception\InvalidServerRequestMessage;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ValidationMiddleware implements MiddlewareInterface
{
    /** @var ServerRequestValidator */
    private $requestValidator;
    /** @var ResponseValidator */
    private $responseValidator;

    public function __construct(ServerRequestValidator $requestValidator, ResponseValidator $responseValidator)
    {
        $this->requestValidator  = $requestValidator;
        $this->responseValidator = $responseValidator;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 1. Validate request
        try {
            $matchedOASOperation = $this->requestValidator->validate($request);
        } catch (ValidationFailed $e) {
            throw InvalidServerRequestMessage::because($e);
        }

        // 2. Process request
        $response = $handler->handle($request);

        // 3. Validate response
        try {
            $this->responseValidator->validate($matchedOASOperation, $response);
        } catch (ValidationFailed $e) {
            throw InvalidResponseMessage::because($e);
        }

        return $response;
    }
}
