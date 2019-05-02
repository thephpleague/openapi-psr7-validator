<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use cebe\openapi\Reader;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use OpenAPIValidation\PSR7\Validator;

class MessageCookiesTest extends BaseValidatorTest
{

//    public function test_it_validates_request_with_cookies_green()
//    {
//        $request = $this->makeGoodServerRequest('/path1', 'get')
//                        ->withHeader('Cookie', 'session_id="string value"; debug=0');
//
//        $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
//        $validator->validate($request);
//        $this->addToAssertionCount(1);
//    }

//    public function test_it_validates_request_with_missed_cookie_red()
//    {
//        $request = $this->makeGoodServerRequest('/path1', 'get')
//                        ->withHeader('Cookie', 'debug=0');
//
//
//        $validator = new Validator(Reader::readFromYamlFile($this->apiSpecFile));
//        $validator->validateRequest($request);
//        $this->fail("Exception expected");
//    }

}
