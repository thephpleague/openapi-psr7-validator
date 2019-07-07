<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use OpenAPIValidation\PSR7\Exception\MultipleOperationsMismatchForRequest;
use OpenAPIValidation\PSR7\Exception\NoOperation;
use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use OpenAPIValidation\PSR7\Validators\BodyValidator;
use OpenAPIValidation\PSR7\Validators\CookiesValidator;
use OpenAPIValidation\PSR7\Validators\HeadersValidator;
use OpenAPIValidation\PSR7\Validators\PathValidator;
use OpenAPIValidation\PSR7\Validators\QueryArgumentsValidator;
use OpenAPIValidation\PSR7\Validators\SecurityValidator;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use function count;
use function strtolower;

class ServerRequestValidator implements ReusableSchema
{
    /** @var OpenApi */
    protected $openApi;
    /** @var SpecFinder */
    protected $finder;

    public function __construct(OpenApi $schema)
    {
        $this->openApi = $schema;
        $this->finder  = new SpecFinder($this->openApi);
    }

    public function getSchema() : OpenApi
    {
        return $this->openApi;
    }

    /**
     * @return OperationAddress which matched the Request
     *
     * @throws ValidationFailed
     */
    public function validate(ServerRequestInterface $serverRequest) : OperationAddress
    {
        $path   = $serverRequest->getUri()->getPath();
        $method = strtolower($serverRequest->getMethod());

        // 0. Find matching operations
        // If there is only one - then proceed with checking
        // If there are multiple candidates, then check each one, if all fail - we don't know which one supposed to be the one, so we need to throw an exception like
        // "This request matched operations A,B and C, but mismatched its schemas."
        $matchingOperationsAddrs = $this->finder->findMatchingOperations($serverRequest);

        if (! $matchingOperationsAddrs) {
            throw NoOperation::fromPathAndMethod($path, $method);
        }

        // Single match is the most desirable variant, because we reduce ambiguity down to zero
        if (count($matchingOperationsAddrs) === 1) {
            $this->validateAddress($matchingOperationsAddrs[0], $serverRequest);

            return $matchingOperationsAddrs[0];
        }

        // there are multiple matching operations, this is bad, because if none of them match the message
        // then we cannot say reliably which one intended to match
        foreach ($matchingOperationsAddrs as $matchedAddr) {
            try {
                $this->validateAddress($matchedAddr, $serverRequest);

                return $matchedAddr; // Good, operation matched and request is valid against it, stop here
            } catch (Throwable $e) {
                // that operation did not match
            }
        }

        // no operation matched at all...
        throw MultipleOperationsMismatchForRequest::fromMatchedAddrs($matchingOperationsAddrs);
    }

    /**
     * @throws ValidationFailed
     */
    protected function validateAddress(OperationAddress $addr, ServerRequestInterface $serverRequest) : void
    {
        $this->validateHeaders($addr, $serverRequest);

        $this->validateCookies($addr, $serverRequest);

        $this->validateBody($addr, $serverRequest);

        $this->validateQueryArgs($addr, $serverRequest);

        $this->validatePath($addr, $serverRequest);

        $this->validateSecurity($addr, $serverRequest);
    }

    /**
     * @throws ValidationFailed
     */
    protected function validateHeaders(OperationAddress $addr, ServerRequestInterface $serverRequest) : void
    {
        $headersValidator = new HeadersValidator($this->finder);
        $headersValidator->validate($addr, $serverRequest);
    }

    /**
     * @throws ValidationFailed
     */
    private function validateCookies(OperationAddress $addr, ServerRequestInterface $serverRequest) : void
    {
        $cookieValidator = new CookiesValidator($this->finder);
        $cookieValidator->validate($addr, $serverRequest);
    }

    /**
     * @throws ValidationFailed
     */
    private function validateBody(OperationAddress $addr, ServerRequestInterface $serverRequest) : void
    {
        $bodyValidator = new BodyValidator($this->finder);
        $bodyValidator->validate($addr, $serverRequest);
    }

    /**
     * @throws ValidationFailed
     */
    private function validateQueryArgs(OperationAddress $addr, ServerRequestInterface $serverRequest) : void
    {
        $queryArgumentsValidator = new QueryArgumentsValidator($this->finder);
        $queryArgumentsValidator->validate($addr, $serverRequest);
    }

    /**
     * @throws ValidationFailed
     */
    private function validatePath(OperationAddress $addr, ServerRequestInterface $serverRequest) : void
    {
        $pathValidator = new PathValidator($this->finder);
        $pathValidator->validate($addr, $serverRequest);
    }

    /**
     * @throws ValidationFailed
     */
    private function validateSecurity(OperationAddress $addr, ServerRequestInterface $serverRequest) : void
    {
        $pathValidator = new SecurityValidator($this->finder);
        $pathValidator->validate($addr, $serverRequest);
    }
}
