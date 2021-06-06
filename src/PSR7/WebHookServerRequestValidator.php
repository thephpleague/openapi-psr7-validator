<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Schema;
use League\OpenAPIValidation\PSR7\Exception\MultipleOperationsMismatchForRequest;
use League\OpenAPIValidation\PSR7\Exception\NoOperation;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\Validators\BodyValidator\BodyValidator;
use League\OpenAPIValidation\PSR7\Validators\CookiesValidator\CookiesValidator;
use League\OpenAPIValidation\PSR7\Validators\HeadersValidator;
use League\OpenAPIValidation\PSR7\Validators\PathValidator;
use League\OpenAPIValidation\PSR7\Validators\QueryArgumentsValidator;
use League\OpenAPIValidation\PSR7\Validators\SecurityValidator;
use League\OpenAPIValidation\PSR7\Validators\ValidatorChain;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function count;
use function strtolower;

class WebHookServerRequestValidator implements ReusableSchema
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
     * @return Schema matching the webhook event
     *
     * @throws ValidationFailed
     */
    public function validate(ServerRequestInterface $serverRequest): Schema
    {
        $event   = $serverRequest->getHeaderLine('X-GitHub-Event');
        $method = strtolower($serverRequest->getMethod());

        if (! $this->openApi->webhooks->hasWebHook($event)) {
            throw NoOperation::fromPathAndMethod($event, $method);
        }

//        var_export($this->openApi->webhooks->getWebHook($event));
//        var_export($matchingOperationsAddrs = $this->findMatchingOperations($serverRequest));
        foreach ($this->findMatchingOperations($serverRequest) as $operation) {
            $this->validator->validate(new OperationAddress());
        }
        // no operation matched at all...
        throw MultipleOperationsMismatchForRequest::fromMatchedAddrs($matchingOperationsAddrs);
    }

    /**
     * Check the openapi spec and find matching operations(path+method)
     * This should consider path parameters as well
     * "/users/12" should match both ["/users/{id}", "/users/{group}"]
     *
     * @return OperationAddress[]
     */
    private function findMatchingOperations(ServerRequestInterface $request): array
    {
        $pathFinder = new WebHookFinder($this->openApi, $request->getHeaderLine('X-GitHub-Event'), $request->getMethod());

        return $pathFinder->search();
    }
}
