<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\Exception\MultipleOperationsMismatchForRequest;
use League\OpenAPIValidation\PSR7\Exception\NoOperation;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\Validators\BodyValidator\BodyValidator;
use League\OpenAPIValidation\PSR7\Validators\CookiesValidator\CookiesValidator;
use League\OpenAPIValidation\PSR7\Validators\HeadersValidator;
use League\OpenAPIValidation\PSR7\Validators\PathValidator;
use League\OpenAPIValidation\PSR7\Validators\QueryArgumentsValidator;
use League\OpenAPIValidation\PSR7\Validators\SecurityValidator;
use League\OpenAPIValidation\PSR7\Validators\ValidatorChain;
use Psr\Http\Message\RequestInterface;
use Throwable;

use function count;
use function strtolower;

class RequestValidator implements ReusableSchema
{
    /** @var OpenApi */
    protected $openApi;
    /** @var MessageValidator */
    protected $validator;

    public function __construct(OpenApi $schema)
    {
        $this->openApi   = $schema;
        $finder          = new SpecFinder($this->openApi);
        $this->validator = new ValidatorChain(
            new HeadersValidator($finder),
            new CookiesValidator($finder),
            new BodyValidator($finder),
            new QueryArgumentsValidator($finder),
            new PathValidator($finder),
            new SecurityValidator($finder)
        );
    }

    public function getSchema(): OpenApi
    {
        return $this->openApi;
    }

    /**
     * @return OperationAddress which matched the Request
     *
     * @throws ValidationFailed
     */
    public function validate(RequestInterface $request): OperationAddress
    {
        $path   = $request->getUri()->getPath();
        $method = strtolower($request->getMethod());

        $pathFinder = new PathFinder($this->openApi, (string) $request->getUri(), $request->getMethod());

        // 0. Find matching operations
        // If there is only one - then proceed with checking
        // If there are multiple candidates, then check each one, if all fail - we don't know which one supposed to be the one, so we need to throw an exception like
        // "This request matched operations A,B and C, but mismatched its schemas."
        $matchingOperationsAddrs = $pathFinder->search();

        if (! $matchingOperationsAddrs) {
            if ($pathFinder->getPathMatches() !== []) {
                throw NoOperation::fromPathAndMethod($path, $method);
            }

            throw NoPath::fromPath($path);
        }

        // Single match is the most desirable variant, because we reduce ambiguity down to zero
        if (count($matchingOperationsAddrs) === 1) {
            $this->validator->validate($matchingOperationsAddrs[0], $request);

            return $matchingOperationsAddrs[0];
        }

        // there are multiple matching operations, this is bad, because if none of them match the message
        // then we cannot say reliably which one intended to match
        foreach ($matchingOperationsAddrs as $matchedAddr) {
            try {
                $this->validator->validate($matchedAddr, $request);

                return $matchedAddr; // Good, operation matched and request is valid against it, stop here
            } catch (Throwable $e) {
                // that operation did not match
            }
        }

        // no operation matched at all...
        throw MultipleOperationsMismatchForRequest::fromMatchedAddrs($matchingOperationsAddrs);
    }
}
