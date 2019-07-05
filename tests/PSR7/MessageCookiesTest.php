<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use OpenAPIValidation\PSR7\Exception\Request\MissedRequestCookie;
use OpenAPIValidation\PSR7\Exception\Request\RequestCookiesMismatch;
use OpenAPIValidation\PSR7\Exception\Response\MissedResponseHeader;
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

    public function testItValidatesResponseMissesSetcookieHeaderGreen() : void
    {
        $addr     = new ResponseAddress('/cookies', 'post', 200);
        $response = $this->makeGoodResponse($addr->path(), $addr->method())->withoutHeader('Set-Cookie');

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
            $validator->validate($addr, $response);
        } catch (MissedResponseHeader $e) {
            $this->assertEquals('Set-Cookie', $e->headerName());
        }
    }

    public function testItValidatesRequestWithMissedCookieRed() : void
    {
        $addr    = new OperationAddress('/cookies', 'post');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withCookieParams([]);

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
            $validator->validate($request);
            $this->fail('Exception expected');
        } catch (MissedRequestCookie $e) {
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
            $this->assertEquals('session_id', $e->cookieName());
        }
    }

    public function testItValidatesRequestWithInvalidCookieValueRed() : void
    {
        $addr    = new OperationAddress('/cookies', 'post');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withCookieParams(['session_id' => 'goodvalue', 'debug' => 'bad value']);

        try {
            $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
            $validator->validate($request);
            $this->fail('Exception expected');
        } catch (RequestCookiesMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
        }
    }
}
