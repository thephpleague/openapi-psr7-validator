<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use cebe\openapi\Reader;
use OpenAPIValidation\PSR7\Exception\MissedRequestHeader;
use OpenAPIValidation\PSR7\Exception\RequestBodyMismatch;
use OpenAPIValidation\PSR7\Exception\RequestHeadersMismatch;
use OpenAPIValidation\PSR7\Exception\UnexpectedRequestContentType;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use function GuzzleHttp\Psr7\stream_for;

class ServerRequestTest extends BaseValidatorTest
{

    public function test_it_validates_message_green()
    {
        $addr    = new OperationAddress('/path1', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method());

        $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
        $validator->validate($addr, $request);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_body_green()
    {
        $addr    = new OperationAddress('/request-body', 'post');
        $body    = ['name' => 'Alex'];
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
                        ->withBody(stream_for(json_encode($body)));

        $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
        $validator->validate($addr, $request);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_body_has_invalid_payload_red()
    {
        $addr    = new OperationAddress('/request-body', 'post');
        $body    = ['name' => 1000];
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
                        ->withBody(stream_for(json_encode($body)));

        try {
            $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validate($addr, $request);
        } catch (RequestBodyMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
        }
    }

    public function test_it_validates_body_has_unexpected_type_red()
    {
        $addr    = new OperationAddress('/request-body', 'post');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
                        ->withoutHeader('Content-Type')
                        ->withHeader('Content-Type', 'unexpected/content');

        try {
            $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validate($addr, $request);
        } catch (UnexpectedRequestContentType $e) {
            $this->assertEquals('unexpected/content', $e->contentType());
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
        }
    }


    public function test_it_validates_message_wrong_header_value_red()
    {
        $addr    = new OperationAddress('/path1', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())->withHeader('Header-A', 'wrong value');

        try {
            $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validate($addr, $request);
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
            $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validate($addr, $request);
            $this->fail("Exception expected");
        } catch (MissedRequestHeader $e) {
            $this->assertEquals('Header-A', $e->headerName());
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
        }

    }
}
