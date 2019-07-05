<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\Exception\Request\RequestPathParameterMismatch;
use OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class PathParametersTest extends TestCase
{
    public function testItValidatesRequestQueryArgumentsGreen() : void
    {
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/users/admin');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesRequestMissedQueryArgumentsGreen() : void
    {
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/users/wrong');

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
            $validator->validate($request);
        } catch (RequestPathParameterMismatch $e) {
            $this->assertEquals('/users/wrong', $e->actualPath());
            $this->assertEquals('/users/{group}', $e->path());
            $this->assertEquals('get', $e->method());
        }
    }

    public function testItAllowsOptionalParametersGreen() : void
    {
        // Schema allows optional header,cookie, and query parameters
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        // Request does not have any of parameters (which should be valid)
        $request = new ServerRequest('get', '/optional/params');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }
}
