<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators;

use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\SecurityRequirement;
use cebe\openapi\spec\SecurityScheme;
use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;
use function count;
use function preg_match;
use function sprintf;

class SecurityValidator
{
    /** @var SecurityScheme[] */
    protected $securitySchemes;

    /**
     * @param SecurityRequirement[] $securitySpecs
     * @param SecurityScheme[]      $securitySchemesSpec
     *
     * @throws ValidationFailed
     */
    public function validate(MessageInterface $message, array $securitySpecs, array $securitySchemesSpec) : void
    {
        // Note: Security schemes support OR/AND union
        // That is, security is an array of hashmaps, where each hashmap contains one or more named security schemes.
        // Items in a hashmap are combined using logical AND, and array items are combined using logical OR.
        // Security schemes combined via OR are alternatives – any one can be used in the given context.
        // Security schemes combined via AND must be used simultaneously in the same request.

        $this->securitySchemes = $securitySchemesSpec;

        if ($message instanceof ServerRequestInterface) {
            $this->validateServerRequest($message, $securitySpecs);
        }

        // TODO should implement validation for Request classes
    }

    /**
     * @param Parameter[] $securitySpecs
     *
     * @throws ValidationFailed
     */
    private function validateServerRequest(ServerRequestInterface $request, array $securitySpecs) : void
    {
        if (! count($securitySpecs)) {
            // no auth needed
            return;
        }

        // OR-union: any of security schemes can match
        foreach ($securitySpecs as $spec) {
            try {
                $this->validateSecurityScheme($request, $spec);

                return; // this sucurity schema matched, request is valid, stop here
            } catch (ValidationFailed $e) {
                // that security schema did not match
            }
        }

        // no schema matched, that is bad
        throw new ValidationFailed('No security schema matched', 600);
    }

    /**
     * @throws ValidationFailed
     */
    private function validateSecurityScheme(ServerRequestInterface $request, SecurityRequirement $spec) : void
    {
        // Here I implement AND-union
        // Each SecurityRequirement contains 1+ security [schema_name=>scopes]
        // Scopes are not used for the purpose of validation

        $shouldMatchSchemesCount = count((array) $spec->getSerializableData());

        foreach ($spec->getSerializableData() as $securityScheme => $scopes) {
            if (! isset($this->securitySchemes[$securityScheme])) {
                throw new ValidationFailed(sprintf("Mentioned security scheme '%s' not found in OAS spec", $securityScheme));
            }
            $securityScheme = $this->securitySchemes[$securityScheme];

            switch ($securityScheme->type) {
                case 'http':
                    $this->validateHTTPSecurityScheme($request, $securityScheme);
                    break;
                case 'apiKey':
                    $this->validateApiKeySecurityScheme($request, $securityScheme);
                    break;
            }

            // security query argument exists, good
            $shouldMatchSchemesCount--;
        }

        // Check that all AND-united security schemes matched
        if ($shouldMatchSchemesCount) {
            throw new ValidationFailed('Request did not match all of given security schemes');
        }
    }


    /**
     * @throws ValidationFailed
     */
    private function validateHTTPSecurityScheme(ServerRequestInterface $request, SecurityScheme $securityScheme) : void
    {
        // Supported schemas: https://www.iana.org/assignments/http-authschemes/http-authschemes.xhtml

        // Token should be passed in TLS session, in header: `Authorization:....`
        if (! $request->hasHeader('Authorization')) {
            throw new ValidationFailed('', 611);
        }

        switch ($securityScheme->scheme) {
            case 'basic':
                // Described in https://tools.ietf.org/html/rfc7617
                if (! preg_match('#^Basic #', $request->getHeader('Authorization')[0])) {
                    throw new ValidationFailed('', 612);
                }

                break;
            case 'bearer':
                // Described in https://tools.ietf.org/html/rfc6750
                if (! preg_match('#^Bearer #', $request->getHeader('Authorization')[0])) {
                    throw new ValidationFailed('', 612);
                }

                break;
        }
    }

    /**
     * @throws ValidationFailed
     */
    private function validateApiKeySecurityScheme(ServerRequestInterface $request, SecurityScheme $securityScheme) : void
    {
        switch ($securityScheme->in) {
            case 'query':
                if (! isset($request->getQueryParams()[$securityScheme->name])) {
                    throw new ValidationFailed(sprintf("Absent query argument '%s'", $securityScheme->name), 601);
                }
                break;
            case 'header':
                if (! $request->hasHeader($securityScheme->name)) {
                    throw new ValidationFailed(sprintf("Absent header '%s'", $securityScheme->name), 601);
                }
                break;
            case 'cookie':
                if (! isset($request->getCookieParams()[$securityScheme->name])) {
                    throw new ValidationFailed(sprintf("Absent cookie '%s'", $securityScheme->name), 601);
                }
                break;
        }
    }
}
