<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use GuzzleHttp\Psr7\Uri;
use OpenAPIValidation\PSR7\Exception\Request\MissedRequestQueryArgument;
use OpenAPIValidation\PSR7\Exception\Request\RequestQueryArgumentMismatch;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\ServerRequestValidator;

final class QueryArgumentsTest extends BaseValidatorTest
{
    public function test_it_validates_request_query_arguments_green() : void
    {
        $request = $this->makeGoodServerRequest('/read', 'get');

        $validator = ServerRequestValidator::fromYamlFile($this->apiSpecFile);
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_request_missed_query_arguments_green() : void
    {
        $addr    = new OperationAddress('/read', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withUri(new Uri('/read'))
            ->withQueryParams([]);

        try {
            $validator = ServerRequestValidator::fromYamlFile($this->apiSpecFile);
            $validator->validate($request);
        } catch (MissedRequestQueryArgument $e) {
            $this->assertEquals($addr->path(), $e->addr()->path());
            $this->assertEquals($addr->method(), $e->addr()->method());
            $this->assertEquals('limit', $e->queryArgumentName());
        }
    }

    public function test_it_validates_request_invalid_query_arguments_green() : void
    {
        $addr    = new OperationAddress('/read', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withUri(new Uri('/read?limit=wrong'))
            ->withQueryParams(['limit' => 'wronng', 'offset' => 0]);

        try {
            $validator = ServerRequestValidator::fromYamlFile($this->apiSpecFile);
            $validator->validate($request);
        } catch (RequestQueryArgumentMismatch $e) {
            $this->assertEquals($addr->path(), $e->path());
            $this->assertEquals($addr->method(), $e->method());
        }
    }
}
