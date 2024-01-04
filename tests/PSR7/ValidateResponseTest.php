<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidHeaders;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

use function json_encode;

final class ValidateResponseTest extends BaseValidatorTest
{
    public function testItValidatesMessageGreen(): void
    {
        $response = $this->makeGoodResponse('/path1', 'get');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate(new OperationAddress('/path1', 'get'), $response);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesMessageWithReferencesGreen(): void
    {
        $body     = [
            'name' => 'good name',
            'age'  => 100,
        ];
        $response = (new Response())
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Utils::streamFor(json_encode($body)));

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate(new OperationAddress('/ref', 'post'), $response);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesBinaryResponseGreen(): void
    {
        $response = $this->makeGoodResponse('/path1', 'get')
                         ->withHeader('Content-Type', 'image/jpeg')
                         ->withBody(Utils::streamFor(__DIR__ . '/../stubs/image.jpg'));

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate(new OperationAddress('/path1', 'get'), $response);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesMessageWrongBodyValueRed(): void
    {
        $addr     = new OperationAddress('/path1', 'get');
        $body     = [];
        $response = $this->makeGoodResponse('/path1', 'get')->withBody(Utils::streamFor(json_encode($body)));

        $this->expectException(InvalidBody::class);
        $this->expectExceptionMessage(
            'Body does not match schema for content-type "application/json" for Response [get /path1 200]'
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate($addr, $response);
    }

    public function testItValidatesMessageWrongHeaderValueRed(): void
    {
        $addr = new OperationAddress('/path1', 'get');

        $response = $this->makeGoodResponse('/path1', 'get')->withHeader('Header-C', 'wrong value');

        $this->expectException(InvalidHeaders::class);
        $this->expectExceptionMessage(
            'Value "wrong value" for header "Header-C" is invalid for Response [get /path1 200]'
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate($addr, $response);
    }

    public function testItValidatesMessageMissesHeaderRed(): void
    {
        $addr = new OperationAddress('/path1', 'get');

        $response = $this->makeGoodResponse('/path1', 'get')->withoutHeader('Header-B');

        $this->expectException(InvalidHeaders::class);
        $this->expectExceptionMessage('Missing required header "Header-B" for Response [get /path1 200]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate($addr, $response);
    }

    public function testItValidatesEmptyBodyResponseGreen(): void
    {
        $addr     = new OperationAddress('/empty', 'post');
        $response = new Response(204); // no body response

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate($addr, $response);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesDefaultBodyResponseGreen(): void
    {
        $addr     = new OperationAddress('/empty', 'patch'); // "patch" contains "default" response definition
        $response = new Response(404); // dummy any status code

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate($addr, $response);
        $this->addToAssertionCount(1);
    }
}
