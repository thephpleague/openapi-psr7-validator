<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidationTests\PSR7;


use cebe\openapi\Reader;
use GuzzleHttp\Psr7\Uri;
use OpenAPIValidation\PSR7\Exception\MissedRequestQueryArgument;
use OpenAPIValidation\PSR7\Exception\RequestQueryArgumentMismatch;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\ServerRequestValidator;

class QueryArgumentsTest extends BaseValidatorTest
{
    public function test_it_validates_request_query_arguments_green()
    {
        $addr    = new OperationAddress("/read", "get");
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method());

        $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
        $validator->validate($addr, $request);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_request_missed_query_arguments_green()
    {
        $addr    = new OperationAddress("/read", "get");
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
                        ->withUri(new Uri("/read"))
                        ->withQueryParams([]);

        try {
            $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validate($addr, $request);
        } catch (MissedRequestQueryArgument $e) {
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
            $this->assertEquals('limit', $e->queryArgumentName());
        }

    }

    public function test_it_validates_request_invalid_query_arguments_green()
    {
        $addr    = new OperationAddress("/read", "get");
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
                        ->withUri(new Uri("/read?limit=wrong"))
                        ->withQueryParams(['limit' => 'wronng', 'offset' => 0]);

        try {
            $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
            $validator->validate($addr, $request);
        } catch (RequestQueryArgumentMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
        }

    }
}