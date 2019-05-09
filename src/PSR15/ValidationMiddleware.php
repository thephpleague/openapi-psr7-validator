<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR15;


use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use OpenAPIValidation\PSR7\ResponseValidator;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationMiddleware implements MiddlewareInterface
{
    /** @var OpenApi */
    private $oas;

    /**
     * @param OpenApi $oas
     */
    function __construct(OpenApi $oas)
    {
        $this->oas = $oas;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 1. Validate request
        $validator           = new ServerRequestValidator($this->oas);
        $matchedOASOperation = $validator->validate($request);

        // 2. Response
        $response  = $handler->handle($request);
        $validator = new ResponseValidator($this->oas);
        $validator->validate($matchedOASOperation, $response);

        return $response;
    }
}