<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use OpenAPIValidation\PSR7\Exception\Request\MissedRequestHeader;
use OpenAPIValidation\PSR7\Exception\Request\RequestBodyMismatch;
use OpenAPIValidation\PSR7\Exception\Request\RequestHeadersMismatch;
use OpenAPIValidation\PSR7\Exception\Request\UnexpectedRequestContentType;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\ValidatorBuilder;
use function GuzzleHttp\Psr7\stream_for;
use function json_encode;

final class ServerRequestTest extends BaseValidatorTest
{
    public function testItValidatesMessageGreen() : void
    {
        $request = $this->makeGoodServerRequest('/path1', 'get');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServiceRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesBodyGreen() : void
    {
        $body    = ['name' => 'Alex'];
        $request = $this->makeGoodServerRequest('/request-body', 'post')
            ->withBody(stream_for(json_encode($body)));

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServiceRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesBodyHasInvalidPayloadRed() : void
    {
        $addr    = new OperationAddress('/request-body', 'post');
        $body    = ['name' => 1000];
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withBody(stream_for(json_encode($body)));

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServiceRequestValidator();
            $validator->validate($request);
        } catch (RequestBodyMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
        }
    }

    public function testItValidatesBodyHasUnexpectedTypeRed() : void
    {
        $addr    = new OperationAddress('/request-body', 'post');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withoutHeader('Content-Type')
            ->withHeader('Content-Type', 'unexpected/content');

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServiceRequestValidator();
            $validator->validate($request);
        } catch (UnexpectedRequestContentType $e) {
            $this->assertEquals('unexpected/content', $e->contentType());
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
        }
    }

    public function testItValidatesMessageWrongHeaderValueRed() : void
    {
        $addr    = new OperationAddress('/path1', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())->withHeader('Header-A', 'wrong value');

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServiceRequestValidator();
            $validator->validate($request);
            $this->fail('Exception expected');
        } catch (RequestHeadersMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
        }
    }

    public function testItValidatesMessageMissedHeaderRed() : void
    {
        $addr    = new OperationAddress('/path1', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())->withoutHeader('Header-A');

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServiceRequestValidator();
            $validator->validate($request);
            $this->fail('Exception expected');
        } catch (MissedRequestHeader $e) {
            $this->assertEquals('Header-A', $e->headerName());
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
        }
    }
}
