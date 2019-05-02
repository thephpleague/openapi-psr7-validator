<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use cebe\openapi\Reader;
use OpenAPIValidation\PSR7\Exception\MissedRequestHeader;
use OpenAPIValidation\PSR7\Exception\RequestHeadersMismatch;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\Validator;

class ValidateRequestTest extends BaseValidatorTest
{

    public function test_it_validates_message_green()
    {
        $addr    = new OperationAddress('/path1', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method());

        $validator = new Validator(Reader::readFromYamlFile($this->apiSpecFile));
        $validator->validateServerRequest($addr, $request);
        $this->addToAssertionCount(1);
    }


    public function test_it_validates_message_wrong_header_value_red()
    {
        $addr    = new OperationAddress('/path1', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())->withHeader('Header-A', 'wrong value');

        try {
            $validator = new Validator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validateServerRequest($addr, $request);
            $this->fail("Exception expected");
        } catch (RequestHeadersMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
        }

    }

    public function test_it_validates_message_missed_header_red()
    {
        $addr    = new OperationAddress('/path1', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())->withoutHeader('Header-A');

        try {
            $validator = new Validator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validateServerRequest($addr, $request);
            $this->fail("Exception expected");
        } catch (MissedRequestHeader $e) {
            $this->assertEquals('Header-A', $e->headerName());
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
        }

    }
}
