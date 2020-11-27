<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\Validators\BodyValidator\BodyValidator;
use League\OpenAPIValidation\PSR7\Validators\HeadersValidator;
use League\OpenAPIValidation\PSR7\Validators\ValidatorChain;
use Psr\Http\Message\ResponseInterface;

final class CallbackResponseValidator implements ReusableSchema
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
            new BodyValidator($finder)
        );
    }

    public function getSchema(): OpenApi
    {
        return $this->openApi;
    }

    /**
     * @throws ValidationFailed
     */
    public function validate(CallbackAddress $opAddr, ResponseInterface $serverRequest): void
    {
        $opAddr = new CallbackResponseAddress(
            $opAddr->path(),
            $opAddr->method(),
            $opAddr->callbackName(),
            $opAddr->callbackMethod(),
            $serverRequest->getStatusCode()
        );
        $this->validator->validate($opAddr, $serverRequest);
    }
}
