<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use OpenAPIValidation\PSR7\Exception\Response\MissedResponseHeader;
use OpenAPIValidation\PSR7\Exception\Response\ResponseBodyMismatch;
use OpenAPIValidation\PSR7\Exception\Response\ResponseHeadersMismatch;
use OpenAPIValidation\PSR7\Exception\Response\UnexpectedResponseContentType;
use OpenAPIValidation\PSR7\Exception\Response\UnexpectedResponseHeader;
use OpenAPIValidation\PSR7\Validators\Body;
use OpenAPIValidation\PSR7\Validators\Headers;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ResponseValidator
{
    use SpecFinder;

    /** @var OpenApi */
    protected $openApi;

    public function __construct(OpenApi $schema)
    {
        $this->openApi = $schema;
    }

    public function validate(OperationAddress $opAddr, ResponseInterface $response) : void
    {
        $addr = new ResponseAddress($opAddr->path(), $opAddr->method(), $response->getStatusCode());

        // 0. Find appropriate schema to validate against
        $spec = $this->findResponseSpec($addr);

        // 1. Validate Headers
        try {
            $headersValidator = new Headers();
            $headersValidator->validate($response, $spec->headers);
        } catch (Throwable $e) {
            switch ($e->getCode()) {
                case 200:
                    throw UnexpectedResponseHeader::fromResponseAddr($e->getMessage(), $addr);
                    break;
                case 201:
                    throw MissedResponseHeader::fromResponseAddr($e->getMessage(), $addr);
                    break;
                default:
                    throw ResponseHeadersMismatch::fromAddrAndCauseException($addr, $e);
            }
        }

        // 2. Validate Body
        try {
            $bodyValidator = new Body();
            $bodyValidator->validate($response, $spec->content);
        } catch (Throwable $e) {
            switch ($e->getCode()) {
                case 100:
                    throw UnexpectedResponseContentType::fromResponseAddr($e->getMessage(), $addr, $e);
                default:
                    throw ResponseBodyMismatch::fromAddrAndCauseException($addr, $e);
            }
        }
    }
}
