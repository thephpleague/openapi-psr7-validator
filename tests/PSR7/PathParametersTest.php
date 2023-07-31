<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidPath;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class PathParametersTest extends TestCase
{
    public function testItValidatesRequestQueryArgumentsGreen(): void
    {
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/users/admin');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesRequestMissedQueryArgumentsGreen(): void
    {
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/users/wrong');

        $this->expectException(InvalidPath::class);
        $this->expectExceptionMessage('Value "wrong" for parameter "group" is invalid for Request [get /users/{group}]');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
    }

    public function testItValidatesRequestMissedQueryArgumentsForInvalidTypeGreen(): void
    {
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/users/12');

        $this->expectException(InvalidPath::class);
        $this->expectExceptionMessage('Value "12" for parameter "group" is invalid for Request [get /users/{group}]');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
    }

    public function testItAllowsOptionalParametersGreen(): void
    {
        // Schema allows optional header,cookie, and query parameters
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        // Request does not have any of parameters (which should be valid)
        $request = new ServerRequest('get', '/optional/params');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesParsedIntegersGreen(): void
    {
        // In "number/12" an id(12) parsed as a string
        // The implementation should validate this against `type: integer`

        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/number/99');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testParsesFormat(): void
    {
        // dot in path template must be handled with care
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/number/99.json');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesPathParameterArray(): void
    {
        // dot in path template must be handled with care
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/array/1,2,3,99');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesPathParameterSimpleArray(): void
    {
        // dot in path template must be handled with care
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/arrayLabel/.1,2,3,99');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesPathParameterExplodedSimpleArray(): void
    {
        // dot in path template must be handled with care
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/arrayLabelExploded/.1.2.3.99');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesPathParameterMatrixArray(): void
    {
        // dot in path template must be handled with care
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/arrayMatrix/;id=1,2,3,99');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesPathParameterExplodedMatrixArray(): void
    {
        // dot in path template must be handled with care
        $specFile = __DIR__ . '/../stubs/pathParams.yaml';
        $request  = new ServerRequest('get', '/arrayMatrixExploded/;id=1;id=2;id=3;id=99');

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }
}
