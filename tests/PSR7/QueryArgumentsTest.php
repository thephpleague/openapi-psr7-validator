<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\Uri;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidQueryArgs;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

final class QueryArgumentsTest extends BaseValidatorTest
{
    public function testItValidatesServerRequestQueryArgumentsGreen(): void
    {
        $request = $this->makeGoodServerRequest('/read', 'get');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesRequestQueryArgumentsGreen(): void
    {
        $request = $this->makeGoodRequest('/read', 'get');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesServerRequestMissedQueryArgumentsGreen(): void
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

    public function testItValidatesRequestMissedQueryArgumentsGreen(): void
    {
        $addr    = new OperationAddress('/read', 'get');
        $request = $this->makeGoodRequest($addr->path(), $addr->method())
                        ->withUri(new Uri('/read'));

        $this->expectException(InvalidQueryArgs::class);
        $this->expectExceptionMessage('Missing required argument "limit" for Request [get /read]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
    }

    public function testItValidatesServerRequestInvalidQueryArgumentsGreen(): void
    {
        $addr    = new OperationAddress('/read', 'get');
        $request = $this->makeGoodServerRequest($addr->path(), $addr->method())
            ->withUri(new Uri('/read?limit=wrong&offset=0'))
            ->withQueryParams(['limit' => 'wrong', 'offset' => 0]);

        $this->expectException(InvalidQueryArgs::class);
        $this->expectExceptionMessage('Value "wrong" for argument "limit" is invalid for Request [get /read]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
    }

    public function testItValidatesRequestInvalidQueryArgumentsGreen(): void
    {
        $addr    = new OperationAddress('/read', 'get');
        $request = $this->makeGoodRequest($addr->path(), $addr->method())
                        ->withUri(new Uri('/read?limit=wrong&offset=0'));

        $this->expectException(InvalidQueryArgs::class);
        $this->expectExceptionMessage('Value "wrong" for argument "limit" is invalid for Request [get /read]');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
    }
}
