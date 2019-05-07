<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Validators;


use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\SecurityRequirement;
use cebe\openapi\spec\SecurityScheme;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ServerRequestInterface;

class Security
{
    use ValidationStrategy;

    /** @var SecurityScheme[] */
    protected $securitySchemes;

    /**
     * @param MessageInterface $message
     * @param SecurityRequirement[] $securitySpecs
     * @param array $securitySchemesSpec
     * @throws \Exception
     */
    public function validate(MessageInterface $message, array $securitySpecs, array $securitySchemesSpec): void
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
     * @param ServerRequestInterface $request
     * @param Parameter[] $securitySpecs
     */
    private function validateServerRequest(ServerRequestInterface $request, array $securitySpecs): void
    {
        if (!count($securitySpecs)) {
            // no auth needed
            return;
        }

        # OR-union: any of security schemes can match
        foreach ($securitySpecs as $spec) {
            try {
                $this->validateSecurityScheme($request, $spec);
                return; # this sucurity schema matched, request is valid, stop here
            } catch (\Throwable $e) {
                // that security schema did not match
            }
        }

        // no schema matched, that is bad
        throw new \Exception("No security schema matched", 600);
    }

    /**
     * @param ServerRequestInterface $request
     * @param SecurityRequirement $spec
     */
    private function validateSecurityScheme(ServerRequestInterface $request, SecurityRequirement $spec): void
    {
        // Here I implement AND-union
        $shouldMatchSchemesCount = count((array)$spec->getSerializableData());


        foreach ($spec->getSerializableData() as $securityScheme => $scopes) {

            if (!isset($this->securitySchemes[$securityScheme])) {
                throw new \Exception(sprintf("Mentioned security scheme '%s' not found in OAS spec", $securityScheme));
            }
            $securityScheme = $this->securitySchemes[$securityScheme];

            switch ($securityScheme->in) {
                case "query":
                    if (!isset($request->getQueryParams()[$securityScheme->name])) {
                        throw new \Exception(sprintf("Absent query argument '%s'", $securityScheme->name), 601);
                    }

                    # security query argument exists, good
                    $shouldMatchSchemesCount--;

                    break;
                case "header":
                    if (!count($request->getHeaders()[$securityScheme->name])) {
                        throw new \Exception(sprintf("Absent header '%s'", $securityScheme->name), 601);
                    }

                    # security query argument exists, good
                    $shouldMatchSchemesCount--;

                    break;
                case "cookie":
                    if (!isset($request->getCookieParams()[$securityScheme->name])) {
                        throw new \Exception(sprintf("Absent cookie '%s'", $securityScheme->name), 601);
                    }

                    # security query argument exists, good
                    $shouldMatchSchemesCount--;

                    break;
            }
        }

        // Check that all AND-united security schemes matched
        if ($shouldMatchSchemesCount) {
            throw new \Exception("Request did not match all of given security schemes");
        }
    }


}