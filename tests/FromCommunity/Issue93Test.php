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
    protected function makeBadMethodRequest() : RequestInterface
    {
        return new Request('get', '/empty');
    }

    protected function makeBadPathRequest() : RequestInterface
    {
        return new Request('get', '/no-such-path');
    }

    public function testBadMethodRequest() : void
    {
        $request = $this->makeBadMethodRequest();

        $this->expectException(NoOperation::class);
        $this->expectExceptionMessage(
            'OpenAPI spec contains no such operation [/empty,get]'
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        $validator->validate($request);
    }

    public function testBadPathRequest() : void
    {
        $request = $this->makeBadPathRequest();

        $this->expectException(NoPath::class);

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getRequestValidator();
        try {
            $validator->validate($request);
        } catch (NoPath $e) {
            $this->assertEquals(
                NoPath::class,
                get_class($e)
            );
            throw $e;
        }
    }
}
