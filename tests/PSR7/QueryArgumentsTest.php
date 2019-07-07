<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use GuzzleHttp\Psr7\Uri;
use OpenAPIValidation\PSR7\Exception\Validation\InvalidQueryArgs;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\ValidatorBuilder;

final class QueryArgumentsTest extends BaseValidatorTest
{
    public function testItValidatesRequestQueryArgumentsGreen() : void
    {
        $request = $this->makeGoodServerRequest('/read', 'get');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesRequestMissedQueryArgumentsGreen() : void
    {
        $addr    = new OperationAddress('/read', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withUri(new Uri('/read'))
            ->withQueryParams([]);

        $this->expectException(InvalidQueryArgs::class);
        $this->expectExceptionMessage('Missing required argument "limit" for Request [get /read]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
    }

    public function testItValidatesRequestInvalidQueryArgumentsGreen() : void
    {
        $addr    = new OperationAddress('/read', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withUri(new Uri('/read?limit=wrong'))
            ->withQueryParams(['limit' => 'wronng', 'offset' => 0]);

        $this->expectException(InvalidQueryArgs::class);
        $this->expectExceptionMessage('Value "wrong" for argument "limit" is invalid for Request [get /read]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
    }
}
