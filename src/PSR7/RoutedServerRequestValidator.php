<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use OpenAPIValidation\PSR7\Validators\BodyValidator\BodyValidator;
use OpenAPIValidation\PSR7\Validators\CookiesValidator;
use OpenAPIValidation\PSR7\Validators\HeadersValidator;
use OpenAPIValidation\PSR7\Validators\PathValidator;
use OpenAPIValidation\PSR7\Validators\QueryArgumentsValidator;
use OpenAPIValidation\PSR7\Validators\SecurityValidator;
use OpenAPIValidation\PSR7\Validators\ValidatorChain;
use Psr\Http\Message\ServerRequestInterface;

class RoutedServerRequestValidator implements ReusableSchema
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

    public function getSchema() : OpenApi
    {
        return $this->openApi;
    }

    /**
     * @throws ValidationFailed
     */
    public function validate(OperationAddress $opAddr, ServerRequestInterface $serverRequest) : void
    {
        $this->validator->validate($opAddr, $serverRequest);
    }
}
