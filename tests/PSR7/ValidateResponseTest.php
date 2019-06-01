<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use GuzzleHttp\Psr7\Response;
use OpenAPIValidation\PSR7\Exception\Response\MissedResponseHeader;
use OpenAPIValidation\PSR7\Exception\Response\ResponseBodyMismatch;
use OpenAPIValidation\PSR7\Exception\Response\ResponseHeadersMismatch;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\ValidatorBuilder;
use function GuzzleHttp\Psr7\stream_for;
use function json_encode;

final class ValidateResponseTest extends BaseValidatorTest
{
    public function testItValidatesMessageGreen() : void
    {
        $response = $this->makeGoodResponse('/path1', 'get');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate(new OperationAddress('/path1', 'get'), $response);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesBinaryResponseGreen() : void
    {
        $response = $this->makeGoodResponse('/path1', 'get')
            ->withHeader('Content-Type', 'image/jpeg')
            ->withBody(stream_for(__DIR__ . '/../stubs/image.jpg'));

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate(new OperationAddress('/path1', 'get'), $response);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesMessageWrongBodyValueRed() : void
    {
        $addr     = new OperationAddress('/path1', 'get');
        $body     = [];
        $response = $this->makeGoodResponse('/path1', 'get')->withBody(stream_for(json_encode($body)));

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
            $validator->validate($addr, $response);
            $this->fail('Exception expected');
        } catch (ResponseBodyMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
            $this->assertEquals($response->getStatusCode(), $e->responseCode());
        }
    }

    public function testItValidatesMessageWrongHeaderValueRed() : void
    {
        $addr = new OperationAddress('/path1', 'get');

        $response = $this->makeGoodResponse('/path1', 'get')->withHeader('Header-B', 'wrong value');

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
            $validator->validate($addr, $response);
            $this->fail('Exception expected');
        } catch (ResponseHeadersMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
            $this->assertEquals($response->getStatusCode(), $e->responseCode());
        }
    }

    public function testItValidatesMessageMissesHeaderRed() : void
    {
        $addr = new OperationAddress('/path1', 'get');

        $response = $this->makeGoodResponse('/path1', 'get')->withoutHeader('Header-B');

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
            $validator->validate($addr, $response);
            $this->fail('Exception expected');
        } catch (MissedResponseHeader $e) {
            $this->assertEquals('Header-B', $e->headerName());
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
            $this->assertEquals($response->getStatusCode(), $e->addr()->responseCode());
        }
    }

    public function testItValidatesEmptyBodyResponseGreen() : void
    {
        $addr     = new OperationAddress('/empty', 'post');
        $response = new Response(204); // no body response

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate($addr, $response);
        $this->addToAssertionCount(1);
    }
}
