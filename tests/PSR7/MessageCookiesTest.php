<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use cebe\openapi\Reader;
use OpenAPIValidation\PSR7\Exception\MissedRequestCookie;
use OpenAPIValidation\PSR7\Exception\MissedResponseHeader;
use OpenAPIValidation\PSR7\Exception\RequestCookiesMismatch;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\ResponseAddress;
use OpenAPIValidation\PSR7\ResponseValidator;
use OpenAPIValidation\PSR7\ServerRequestValidator;

class MessageCookiesTest extends BaseValidatorTest
{

    public function test_it_validates_request_with_cookies_green()
    {
        $addr    = new OperationAddress("/cookies", "post");
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method());

        $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
        $validator->validate($addr, $request);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_response_with_cookies_green()
    {
        $addr     = new ResponseAddress("/cookies", "post", 200);
        $response = $this->makeGoodResponse($addr->path(), $addr->method());

        $validator = new ResponseValidator(Reader::readFromYamlFile($this->apiSpecFile));
        $validator->validate($addr, $response);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_response_misses_setcookie_header_green()
    {
        $addr     = new ResponseAddress("/cookies", "post", 200);
        $response = $this->makeGoodResponse($addr->path(), $addr->method())->withoutHeader('Set-Cookie');

        try {
            $validator = new ResponseValidator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validate($addr, $response);
        } catch (MissedResponseHeader $e) {
            $this->assertEquals('Set-Cookie', $e->headerName());
        }
    }


    public function test_it_validates_request_with_missed_cookie_red()
    {
        $addr    = new OperationAddress("/cookies", "post");
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
                        ->withCookieParams([]);

        try {
            $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validate($addr, $request);
            $this->fail("Exception expected");
        } catch (MissedRequestCookie $e) {
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
            $this->assertEquals('session_id', $e->cookieName());
        }

    }

    public function test_it_validates_request_with_invalid_cookie_value_red()
    {
        $addr    = new OperationAddress("/cookies", "post");
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
                        ->withCookieParams(['session_id' => 'goodvalue', 'debug' => 'bad value']);

        try {
            $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validate($addr, $request);
            $this->fail("Exception expected");
        } catch (RequestCookiesMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
        }

    }

}
