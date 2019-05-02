<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7;


use cebe\openapi\spec\Header as HeaderSpec;
use OpenAPIValidation\PSR7\Exception\MissedRequestCookie;
use OpenAPIValidation\PSR7\Exception\MissedRequestHeader;
use OpenAPIValidation\PSR7\Exception\RequestBodyMismatch;
use OpenAPIValidation\PSR7\Exception\RequestCookiesMismatch;
use OpenAPIValidation\PSR7\Exception\RequestHeadersMismatch;
use OpenAPIValidation\PSR7\Exception\UnexpectedRequestContentType;
use OpenAPIValidation\PSR7\Exception\UnexpectedRequestHeader;
use OpenAPIValidation\PSR7\Validators\Body;
use OpenAPIValidation\PSR7\Validators\Cookies;
use OpenAPIValidation\PSR7\Validators\Headers;
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

        // 1.1 Validate cookies
        $cookieSpecs = [];
        foreach ($spec->parameters as $p) {
            if ($p->in != "cookie") {
                continue;
            }

            $cookieSpecs[$p->name] = $p->schema;
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

        // 2. Validate Body
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
}