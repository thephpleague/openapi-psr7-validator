<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidPath;
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

        $this->expectException(InvalidPath::class);
        $this->expectExceptionMessage('Value "wrong" for parameter "group" is invalid for Request [get /users/{group}]');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
    }

    public function testItValidatesRequestMissedQueryArgumentsForInvalidTypeGreen() : void
    {
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/users/12');

        $this->expectException(InvalidPath::class);
        $this->expectExceptionMessage('Value "12" for parameter "group" is invalid for Request [get /users/{group}]');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
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
