<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7;


use cebe\openapi\spec\Header as HeaderSpec;
use OpenAPIValidation\PSR7\Exception\NoOperation;
use OpenAPIValidation\PSR7\Exception\Request\MissedRequestCookie;
use OpenAPIValidation\PSR7\Exception\Request\MissedRequestHeader;
use OpenAPIValidation\PSR7\Exception\Request\MissedRequestQueryArgument;
use OpenAPIValidation\PSR7\Exception\Request\MultipleOperationsMismatchForRequest;
use OpenAPIValidation\PSR7\Exception\Request\RequestBodyMismatch;
use OpenAPIValidation\PSR7\Exception\Request\RequestCookiesMismatch;
use OpenAPIValidation\PSR7\Exception\Request\RequestHeadersMismatch;
use OpenAPIValidation\PSR7\Exception\Request\RequestPathParameterMismatch;
use OpenAPIValidation\PSR7\Exception\Request\RequestQueryArgumentMismatch;
use OpenAPIValidation\PSR7\Exception\Request\Security\NoRequestSecurityApiKey;
use OpenAPIValidation\PSR7\Exception\Request\Security\RequestSecurityMismatch;
use OpenAPIValidation\PSR7\Exception\Request\UnexpectedRequestContentType;
use OpenAPIValidation\PSR7\Exception\Request\UnexpectedRequestHeader;
use OpenAPIValidation\PSR7\Validators\Body;
use OpenAPIValidation\PSR7\Validators\Cookies;
use OpenAPIValidation\PSR7\Validators\Headers;
use OpenAPIValidation\PSR7\Validators\Path;
use OpenAPIValidation\PSR7\Validators\QueryArguments;
use OpenAPIValidation\PSR7\Validators\Security;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestValidator extends Validator
{
    /**
     * @param ServerRequestInterface $serverRequest
     * @return OperationAddress which matched the Request
     */
    public function validate(ServerRequestInterface $serverRequest): OperationAddress
    {
        $path   = $serverRequest->getUri()->getPath();
        $method = strtolower($serverRequest->getMethod());

        // 0. Find matching operations
        // If there is only one - then proceed with checking
        // If there are multiple candidates, then check each one, if all fail - we don't know which one supposed to be the one, so we need to throw an exception like
        // "This request matched operations A,B and C, but mismatched its schemas."
        $matchingOperationsAddrs = $this->findMatchingOperations($path, $method);

        if (!$matchingOperationsAddrs) {
            throw NoOperation::fromPathAndMethod($path, $method);
        }

        // Single match is the most desirable variant, because we reduce ambiguity down to zero
        if (count($matchingOperationsAddrs) === 1) {
            $this->validateAddress($matchingOperationsAddrs[0], $serverRequest);
            return $matchingOperationsAddrs[0];
        } else {
            // there are multiple matching operations, this is bad, because if none of them match the message
            // then we cannot say reliably which one intended to match
            foreach ($matchingOperationsAddrs as $matchedAddr) {
                try {
                    $this->validateAddress($matchedAddr, $serverRequest);
                    return $matchedAddr; # Good, operation matched and request is valid against it, stop here
                } catch (\Throwable $e) {
                    // that operation did not match
                }
            }

            // no operation matched at all...
            throw MultipleOperationsMismatchForRequest::fromMatchedAddrs($matchingOperationsAddrs);
        }

    }

    protected function validateAddress(OperationAddress $addr, ServerRequestInterface $serverRequest): void
    {
        // 1. Headers
        $this->validateHeaders($addr, $serverRequest);

        // 2. Cookies
        $this->validateCookies($addr, $serverRequest);

        // 3. Body
        $this->validateBody($addr, $serverRequest);

        // 4. Validate Query arguments
        $this->validateQueryArgs($addr, $serverRequest);

        // 5. Validate path
        $this->validatePath($addr, $serverRequest);

        // 6. Validate security
        $this->validateSecurity($addr, $serverRequest);
    }

    /**
     * @param OperationAddress $addr
     * @param ServerRequestInterface $serverRequest
     * @throws \cebe\openapi\exceptions\TypeErrorException
     */
    protected function validateHeaders(OperationAddress $addr, ServerRequestInterface $serverRequest): void
    {
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

        // 2. Collect path-level params
        $pathSpec = $this->findPathSpec($addr);
        foreach ($pathSpec->parameters as $p) {
            if ($p->in != "header") {
                continue;
            }

            $headerSpecs += [$p->name => $p]; #union won't override
        }

        try {
            $headersValidator = new Headers();
            $headersValidator->validate($serverRequest, $headerSpecs);
        } catch (\Throwable $e) {
            switch ($e->getCode()) {
                case 200:
                    throw UnexpectedRequestHeader::fromOperationAddr($e->getMessage(), $addr, $e);
                    break;
                case 201:
                    throw MissedRequestHeader::fromOperationAddr($e->getMessage(), $addr, $e);
                    break;
                default:
                    throw RequestHeadersMismatch::fromAddrAndCauseException($addr, $e);
            }
        }
    }

    /**
     * @param OperationAddress $addr
     * @param ServerRequestInterface $serverRequest
     */
    private function validateCookies(OperationAddress $addr, ServerRequestInterface $serverRequest): void
    {
        $spec = $this->findOperationSpec($addr);

        $cookieSpecs = [];

        // 1. Find operation level params
        foreach ($spec->parameters as $p) {
            if ($p->in != "cookie") {
                continue;
            }

            $cookieSpecs[$p->name] = $p;
        }

        // 2. Collect path-level params
        $pathSpec = $this->findPathSpec($addr);
        foreach ($pathSpec->parameters as $p) {
            if ($p->in != "cookie") {
                continue;
            }

            $cookieSpecs += [$p->name => $p]; #union won't override
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
     */
    private function validateBody(OperationAddress $addr, ServerRequestInterface $serverRequest): void
    {
        $spec = $this->findOperationSpec($addr);

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


    /**
     * @param OperationAddress $addr
     * @param ServerRequestInterface $serverRequest
     */
    private function validateQueryArgs(OperationAddress $addr, ServerRequestInterface $serverRequest): void
    {
        $spec = $this->findOperationSpec($addr);

        // 1. Collect operation-level params
        $querySpecs = [];

        foreach ($spec->parameters as $p) {
            if ($p->in != "query") {
                continue;
            }

            $querySpecs[$p->name] = $p;
        }

        // 2. Collect path-level params
        $pathSpec = $this->findPathSpec($addr);
        foreach ($pathSpec->parameters as $p) {
            if ($p->in != "query") {
                continue;
            }

            $querySpecs += [$p->name => $p]; #union won't override
        }


        // 3. Validate collected params
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

    /**
     * @param OperationAddress $addr
     * @param ServerRequestInterface $serverRequest
     * @throws \Throwable
     */
    private function validatePath(OperationAddress $addr, ServerRequestInterface $serverRequest): void
    {
        $spec = $this->findOperationSpec($addr);

        // 1. Collect operation-level params
        $pathSpecs = [];

        foreach ($spec->parameters as $p) {
            if ($p->in != "path") {
                continue;
            }

            $pathSpecs[$p->name] = $p;
        }

        // 2. Collect path-level params
        $pathSpec = $this->findPathSpec($addr);
        foreach ($pathSpec->parameters as $p) {
            if ($p->in != "path") {
                continue;
            }

            $pathSpecs += [$p->name => $p]; #union won't override
        }

        // 3. Validate collected params
        try {
            $pathValidator = new Path();
            $pathValidator->validate($serverRequest, $pathSpecs, $addr->path());
        } catch (\Throwable $e) {
            switch ($e->getCode()) {
                default:
                    throw RequestPathParameterMismatch::fromAddrAndCauseException($addr, $serverRequest->getUri()->getPath(), $e);
            }
        }
    }

    /**
     * @param OperationAddress $addr
     * @param ServerRequestInterface $serverRequest
     */
    private function validateSecurity(OperationAddress $addr, ServerRequestInterface $serverRequest): void
    {
        $opSpec = $this->findOperationSpec($addr);

        // 1. Collect security params
        if (property_exists($opSpec->getSerializableData(), 'security')) {
            // security is set on operation level
            $securitySpecs = $opSpec->security;
        } else {
            // security is set on root level (fallback option)
            $securitySpecs = $this->openApi->security;
        }

        // 2. Validate collected params
        try {
            $pathValidator = new Security();
            $pathValidator->validate($serverRequest, $securitySpecs, $this->openApi->components ? $this->openApi->components->securitySchemes : []);
        } catch (\Throwable $e) {
            switch ($e->getCode()) {
                case 601:
                    throw NoRequestSecurityApiKey::fromOperationAddr($addr, $e);
                default:
                    throw RequestSecurityMismatch::fromOperationAddr($addr, $e);
            }
        }
    }

}