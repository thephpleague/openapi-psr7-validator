<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR15;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * This class wraps a PSR-15 style single pass middleware,
 * into an invokable double pass middleware.
 */
final class SlimAdapter implements RequestHandlerInterface
{
    /** @var MiddlewareInterface */
    private $middleware;
    /** @var ResponseInterface */
    private $response;
    /** @var callable */
    private $next;

    public function __construct(MiddlewareInterface $middleware)
    {
        $this->middleware = $middleware;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $this->response = $response;
        $this->next     = $next;

        /* Call the PSR-15 middleware and let it return to our handle()
         * method by passing `$this` as RequestHandler. */
        return $this->middleware->process($request, $this);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->next)($request, $this->response);
    }
}
