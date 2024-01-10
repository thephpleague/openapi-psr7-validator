<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\Utils;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidHeaders;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

use function json_encode;

final class RequestTest extends BaseValidatorTest
{
    public function testItValidatesMessageGreen(): void
    {
        $request = $this->makeGoodRequest('/path1', 'get');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesBodyGreen(): void
    {
        $body    = ['name' => 'Alex'];
        $request = $this->makeGoodRequest('/request-body', 'post')
            ->withBody(Utils::streamFor(json_encode($body)));

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesBodyHasInvalidPayloadRed(): void
    {
        $addr    = new OperationAddress('/request-body', 'post');
        $body    = ['name' => 1000];
        $request = $this->makeGoodRequest($addr->path(), $addr->method())
            ->withBody(Utils::streamFor(json_encode($body)));

        $this->expectException(InvalidBody::class);
        $this->expectExceptionMessage(
            'Body does not match schema for content-type "application/json" for Request [post /request-body]'
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
    }

    public function testItValidatesBodyHasUnexpectedTypeRed(): void
    {
        $addr    = new OperationAddress('/request-body', 'post');
        $request = $this->makeGoodRequest($addr->path(), $addr->method())
            ->withoutHeader('Content-Type')
            ->withHeader('Content-Type', 'unexpected/content');

        $this->expectException(InvalidHeaders::class);
        $this->expectExceptionMessage(
            'Content-Type "unexpected/content" is not expected for Request [post /request-body]'
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
    }

    public function testItValidatesBodyHasEmptyTypeRed(): void
    {
        $addr    = new OperationAddress('/request-body', 'post');
        $request = $this->makeGoodRequest($addr->path(), $addr->method())
            ->withHeader('Content-Type', '');

        $this->expectException(InvalidHeaders::class);
        $this->expectExceptionMessage(
            'Missing required header "Content-Type" for Request [post /request-body]'
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
    }

    public function testItValidatesMessageWrongHeaderValueRed(): void
    {
        $addr    = new OperationAddress('/path1', 'get');
        $request = $this->makeGoodRequest($addr->path(), $addr->method())->withHeader('Header-A', 'wrong value');

        $this->expectException(InvalidHeaders::class);
        $this->expectExceptionMessage('Value "wrong value" for header "Header-A" is invalid for Request [get /path1]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
    }

    public function testItValidatesMessageMissedHeaderRed(): void
    {
        $addr    = new OperationAddress('/path1', 'get');
        $request = $this->makeGoodRequest($addr->path(), $addr->method())->withoutHeader('Header-A');

        $this->expectException(InvalidHeaders::class);
        $this->expectExceptionMessage('Missing required header "Header-A" for Request [get /path1]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
    }
}
