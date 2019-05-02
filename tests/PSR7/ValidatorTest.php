<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use cebe\openapi\Reader;
use GuzzleHttp\Psr7\Response;
use OpenAPIValidation\PSR7\Exception\ResponseBodyMismatch;
use OpenAPIValidation\PSR7\ResponseAddress;
use OpenAPIValidation\PSR7\Validator;
use function GuzzleHttp\Psr7\stream_for;

class ValidatorTest extends BaseValidatorTest
{

    public function test_it_validates_message_green()
    {
        $specFile = __DIR__ . "/../openapi_stubs/api.yaml";

        $body     = ['propA' => 1];
        $response = (new Response())
            ->withHeader('Header-A', 'value A')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(stream_for(json_encode($body)));

        $validator = new Validator(Reader::readFromYamlFile($specFile));
        $validator->validateResponse(new ResponseAddress('/path1', 'get', 200), $response);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_message_wrong_body_value_red()
    {
        $specFile = __DIR__ . "/../openapi_stubs/api.yaml";
        $addr     = new ResponseAddress('/path1', 'get', 200);

        $body     = []; # does not conform to the spec
        $response = (new Response())
            ->withHeader('Content-Type', 'application/json')
            ->withBody(stream_for(json_encode($body)));

        try {
            $validator = new Validator(Reader::readFromYamlFile($specFile));
            $validator->validateResponse($addr, $response);
        } catch (ResponseBodyMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
            $this->assertEquals($addr->responseCode(), $e->responseCode());
        }

    }
}
