<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR15;

use League\OpenAPIValidation\PSR15\Exception\InvalidResponseMessage;
use League\OpenAPIValidation\PSR15\Exception\InvalidServerRequestMessage;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use League\OpenAPIValidation\PSR7\WebHookServerRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class WebHookMiddleware implements MiddlewareInterface
{
    public const SCHEMA_ATTRIBUTE = 'aopuhend opaijwefoi joiawpjoi pefoa p2e';

    /** @var WebHookServerRequestValidator */
    private $requestValidator;

    public function __construct(WebHookServerRequestValidator $requestValidator)
    {
        $this->requestValidator  = $requestValidator;
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
        try {
            $request = $request->withAttribute(self::SCHEMA_ATTRIBUTE, $this->requestValidator->validate($request));
        } catch (ValidationFailed $e) {
            throw InvalidServerRequestMessage::because($e);
        }

        return $handler->handle($request);
    }
}
