<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\Request;
use League\OpenAPIValidation\PSR7\Exception\NoOperation;
use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Tests\PSR7\BaseValidatorTest;
use Psr\Http\Message\RequestInterface;

/**
 * @see https://github.com/thephpleague/openapi-psr7-validator/issues/79
 */
final class Issue93Test extends BaseValidatorTest
{
    public function testBadMethodRequest(): void
    {
        $request = $this->makeBadMethodRequest();

        $this->expectException(NoOperation::class);
        $this->expectExceptionMessage(
            'OpenAPI spec contains no such operation [/empty,get]'
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
    }

    public function testBadPathRequest(): void
    {
        $request = $this->makeBadPathRequest();

        $this->expectException(NoPath::class);
        $this->expectExceptionMessage(
            'OpenAPI spec contains no such operation [/no-such-path]'
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();

        $validator->validate($request);
    }

    protected function makeBadMethodRequest(): RequestInterface
    {
        return new Request('get', '/empty');
    }

    protected function makeBadPathRequest(): RequestInterface
    {
        return new Request('get', '/no-such-path');
    }
}
