<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use OpenAPIValidation\PSR7\Validators\BodyValidator;
use OpenAPIValidation\PSR7\Validators\HeadersValidator;
use Psr\Http\Message\ResponseInterface;

class ResponseValidator implements ReusableSchema
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
     * @throws ValidationFailed
     */
    public function validate(OperationAddress $opAddr, ResponseInterface $response) : void
    {
        $addr = new ResponseAddress($opAddr->path(), $opAddr->method(), $response->getStatusCode());
        $this->validateAddress($addr, $response);
    }

    /**
     * @throws ValidationFailed
     */
    protected function validateAddress(ResponseAddress $addr, ResponseInterface $response) : void
    {
        $this->validateHeaders($response, $addr);
        $this->validateBody($response, $addr);
    }

    /**
     * @throws ValidationFailed
     */
    protected function validateHeaders(ResponseInterface $response, ResponseAddress $addr) : void
    {
        $headersValidator = new HeadersValidator($this->finder);
        $headersValidator->validate($addr, $response);
    }

    /**
     * @throws ValidationFailed
     */
    protected function validateBody(ResponseInterface $response, ResponseAddress $addr) : void
    {
        $bodyValidator = new BodyValidator($this->finder);
        $bodyValidator->validate($addr, $response);
    }
}
