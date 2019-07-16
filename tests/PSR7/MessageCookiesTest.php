<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use OpenAPIValidation\PSR7\Exception\Validation\InvalidCookies;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidHeaders;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\ResponseAddress;
use OpenAPIValidation\PSR7\ValidatorBuilder;

final class MessageCookiesTest extends BaseValidatorTest
{
    public function testItValidatesRequestWithCookiesGreen() : void
    {
        $request = $this->makeGoodServerRequest('/cookies', 'post');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesResponseWithCookiesGreen() : void
    {
        $addr     = new ResponseAddress('/cookies', 'post', 200);
        $response = $this->makeGoodResponse($addr->path(), $addr->method());

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate($addr, $response);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesResponseMissesSetCookieHeaderGreen() : void
    {
        $addr     = new ResponseAddress('/cookies', 'post', 200);
        $response = $this->makeGoodResponse($addr->path(), $addr->method())->withoutHeader('Set-Cookie');

        $this->expectException(InvalidHeaders::class);
        $this->expectExceptionMessage('Missing required header "Set-Cookie" for Response [post /cookies 200]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate($addr, $response);
    }

    public function testItValidatesRequestWithMissedCookieRed() : void
    {
        $addr    = new OperationAddress('/cookies', 'post');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withCookieParams([]);

        $this->expectException(InvalidCookies::class);
        $this->expectExceptionMessage('Missing required cookie "session_id" for Request [post /cookies]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
    }

    public function testItValidatesRequestWithInvalidCookieValueRed() : void
    {
        $addr    = new OperationAddress('/cookies', 'post');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withCookieParams(['session_id' => 'goodvalue', 'debug' => 'bad value']);

        $this->expectException(InvalidCookies::class);
        $this->expectExceptionMessage('Value "bad value" for cookie "debug" is invalid for Request [post /cookies]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
    }
}
