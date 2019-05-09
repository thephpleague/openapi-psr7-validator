<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use cebe\openapi\Reader;
use OpenAPIValidation\PSR7\Exception\Response\MissedResponseHeader;
use OpenAPIValidation\PSR7\Exception\Response\ResponseBodyMismatch;
use OpenAPIValidation\PSR7\Exception\Response\ResponseHeadersMismatch;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\ResponseValidator;
use function GuzzleHttp\Psr7\stream_for;

class ValidateResponseTest extends BaseValidatorTest
{

    public function test_it_validates_message_green()
    {
        $response = $this->makeGoodResponse('/path1', 'get');

        $validator = ResponseValidator::fromYamlFile($this->apiSpecFile);
        $validator->validate(new OperationAddress('/path1', 'get'), $response);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_binary_response_green()
    {
        $response = $this->makeGoodResponse('/path1', 'get')
                         ->withHeader('Content-Type', 'image/jpeg')
                         ->withBody(stream_for(__DIR__ . "/../stubs/image.jpg"));

        $validator = ResponseValidator::fromYamlFile($this->apiSpecFile);
        $validator->validate(new OperationAddress('/path1', 'get'), $response);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_message_wrong_body_value_red()
    {
        $addr     = new OperationAddress('/path1', 'get');
        $body     = [];
        $response = $this->makeGoodResponse('/path1', 'get')->withBody(stream_for(json_encode($body)));

        try {
            $validator = ResponseValidator::fromYamlFile($this->apiSpecFile);
            $validator->validate($addr, $response);
            $this->fail("Exception expected");
        } catch (ResponseBodyMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
            $this->assertEquals($response->getStatusCode(), $e->responseCode());
        }

    }

    public function test_it_validates_message_wrong_header_value_red()
    {
        $addr = new OperationAddress('/path1', 'get');

        $response = $this->makeGoodResponse('/path1', 'get')->withHeader('Header-B', 'wrong value');

        try {
            $validator = ResponseValidator::fromYamlFile($this->apiSpecFile);
            $validator->validate($addr, $response);
            $this->fail("Exception expected");
        } catch (ResponseHeadersMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
            $this->assertEquals($response->getStatusCode(), $e->responseCode());
        }

    }

    public function test_it_validates_message_misses_header_red()
    {
        $addr = new OperationAddress('/path1', 'get');

        $response = $this->makeGoodResponse('/path1', 'get')->withoutHeader('Header-B');

        try {
            $validator = ResponseValidator::fromYamlFile($this->apiSpecFile);
            $validator->validate($addr, $response);
            $this->fail("Exception expected");
        } catch (MissedResponseHeader $e) {
            $this->assertEquals('Header-B', $e->headerName());
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
            $this->assertEquals($response->getStatusCode(), $e->addr()->responseCode());
        }

    }
}
