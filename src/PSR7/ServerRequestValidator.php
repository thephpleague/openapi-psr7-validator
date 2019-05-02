<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7;


use cebe\openapi\spec\Header as HeaderSpec;
use cebe\openapi\spec\Operation as OperationSpec;
use OpenAPIValidation\PSR7\Exception\MissedRequestCookie;
use OpenAPIValidation\PSR7\Exception\MissedRequestHeader;
use OpenAPIValidation\PSR7\Exception\MissedRequestQueryArgument;
use OpenAPIValidation\PSR7\Exception\RequestBodyMismatch;
use OpenAPIValidation\PSR7\Exception\RequestCookiesMismatch;
use OpenAPIValidation\PSR7\Exception\RequestHeadersMismatch;
use OpenAPIValidation\PSR7\Exception\RequestQueryArgumentMismatch;
use OpenAPIValidation\PSR7\Exception\UnexpectedRequestContentType;
use OpenAPIValidation\PSR7\Exception\UnexpectedRequestHeader;
use OpenAPIValidation\PSR7\Validators\Body;
use OpenAPIValidation\PSR7\Validators\Cookies;
use OpenAPIValidation\PSR7\Validators\Headers;
use OpenAPIValidation\PSR7\Validators\QueryArguments;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestValidator extends Validator
{
    /**
     * @param OperationAddress $addr
     * @param ServerRequestInterface $serverRequest
     * @throws \cebe\openapi\exceptions\TypeErrorException
     */
    public function validate(OperationAddress $addr, ServerRequestInterface $serverRequest): void
    {
        // 0. Find appropriate schema to validate against
        $spec = $this->findOperationSpec($addr);

        // 1. Headers
        $this->validateHeaders($addr, $serverRequest, $spec);

        // 2. Cookies
        $this->validateCookies($addr, $serverRequest, $spec);

        // 3. Body
        $this->validateBody($addr, $serverRequest, $spec);

        // 4. Validate Query arguments
        $this->validateQueryArgs($addr, $serverRequest, $spec);

    }

    /**
     * @param OperationAddress $addr
     * @param ServerRequestInterface $serverRequest
     * @param OperationSpec $spec
     * @throws \cebe\openapi\exceptions\TypeErrorException
     */
    protected function validateHeaders(OperationAddress $addr, ServerRequestInterface $serverRequest, OperationSpec $spec): void
    {
        // 1. Validate Headers
        // An API call may require that custom headers be sent with an HTTP request. OpenAPI lets you define custom request headers as in: header parameters.
        $headerSpecs = [];
        foreach ($spec->parameters as $p) {
            if ($p->in != "header") {
                continue;
            }

            $headerData = json_decode(json_encode($p->getSerializableData()), true);
            unset($headerData['in']);
            unset($headerData['name']);
            $headerSpecs[$p->name] = new HeaderSpec($headerData);
        }

        try {
            $headersValidator = new Headers();
            $headersValidator->validate($serverRequest, $headerSpecs);
        } catch (\Throwable $e) {
            switch ($e->getCode()) {
                case 200:
                    throw UnexpectedRequestHeader::fromOperationAddr($e->getMessage(), $addr);
                    break;
                case 201:
                    throw MissedRequestHeader::fromOperationAddr($e->getMessage(), $addr);
                    break;
                default:
                    throw RequestHeadersMismatch::fromAddrAndCauseException($addr, $e);
            }
        }
    }

    /**
     * @param OperationAddress $addr
     * @param ServerRequestInterface $serverRequest
     * @param OperationSpec $spec
     */
    private function validateCookies(OperationAddress $addr, ServerRequestInterface $serverRequest, OperationSpec $spec): void
    {
        $cookieSpecs = [];
        foreach ($spec->parameters as $p) {
            if ($p->in != "cookie") {
                continue;
            }

            $cookieSpecs[$p->name] = $p;
        }

        try {
            $cookieValidator = new Cookies();
            $cookieValidator->validate($serverRequest, $cookieSpecs);
        } catch (\Throwable $e) {
            switch ($e->getCode()) {
                case 301:
                    throw MissedRequestCookie::fromOperationAddr($e->getMessage(), $addr);
                    break;
                default:
                    throw RequestCookiesMismatch::fromAddrAndCauseException($addr, $e);
            }
        }
    }

    /**
     * @param OperationAddress $addr
     * @param ServerRequestInterface $serverRequest
     * @param OperationSpec $spec
     */
    private function validateBody(OperationAddress $addr, ServerRequestInterface $serverRequest, OperationSpec $spec): void
    {
        if (!$spec->requestBody) {
            return;
        }

        try {
            $bodyValidator = new Body();
            $bodyValidator->validate($serverRequest, $spec->requestBody->content);
        } catch (\Throwable $e) {
            switch ($e->getCode()) {
                case 100:
                    throw UnexpectedRequestContentType::fromAddr($e->getMessage(), $addr);
                default:
                    throw RequestBodyMismatch::fromAddrAndCauseException($addr, $e);
            }
        }
    }


    private function validateQueryArgs(OperationAddress $addr, ServerRequestInterface $serverRequest, OperationSpec $spec)
    {
        $querySpecs = [];
        foreach ($spec->parameters as $p) {
            if ($p->in != "query") {
                continue;
            }

            $querySpecs[$p->name] = $p;
        }

        try {
            $queryArgumentsValidator = new QueryArguments();
            $queryArgumentsValidator->validate($serverRequest, $querySpecs);
        } catch (\Throwable $e) {
            switch ($e->getCode()) {
                case 401:
                    throw MissedRequestQueryArgument::fromOperationAddr($e->getMessage(), $addr);
                    break;
                default:
                    throw RequestQueryArgumentMismatch::fromAddrAndCauseException($addr, $e);
            }
        }
    }

}