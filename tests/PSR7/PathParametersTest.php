<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\Exception\Request\RequestPathParameterMismatch;
use OpenAPIValidation\PSR7\ServerRequestValidator;

final class PathParametersTest extends BaseValidatorTest
{
    public function test_it_validates_request_query_arguments_green() : void
    {
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/users/admin');

        $validator = ServerRequestValidator::fromYamlFile($specFile);
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_request_missed_query_arguments_green() : void
    {
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/users/wrong');

        try {
            $validator = ServerRequestValidator::fromYamlFile($specFile);
            $validator->validate($request);
        } catch (RequestPathParameterMismatch $e) {
            $this->assertEquals('/users/wrong', $e->actualPath());
            $this->assertEquals('/users/{group}', $e->path());
            $this->assertEquals('get', $e->method());
        }
    }
}
