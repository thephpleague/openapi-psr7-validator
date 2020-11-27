<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR15;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR15\Exception\InvalidResponseMessage;
use League\OpenAPIValidation\PSR15\Exception\InvalidServerRequestMessage;
use League\OpenAPIValidation\PSR15\ValidationMiddleware;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Tests\PSR7\BaseValidatorTest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationMiddlewareTest extends BaseValidatorTest
{
    /** @return array<mixed> data sets for tests */
    public function dataProvider(): array
    {
        return [
            // Bad Server Request
            [
                new ServerRequest('get', '/unknown'),
                $this->createMock(RequestHandlerInterface::class),
                InvalidServerRequestMessage::class,
            ],
            // Bad Response
            [
                $this->makeGoodServerRequest('/read', 'get'),
                (function () {
                    $mock = $this->createMock(RequestHandlerInterface::class);
                    $mock
                        ->expects($this->once())
                        ->method('handle')
                        ->willReturn(new Response());

                    return $mock;
                })(),
                InvalidResponseMessage::class,
            ],
        ];
    }

    /** @dataProvider dataProvider */
    public function testItReturnsExpectedException(
        ServerRequestInterface $serverRequest,
        RequestHandlerInterface $handler,
        string $expectedExceptionType
    ): void {
        $builder = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile);

        $middleware = new ValidationMiddleware(
            $builder->getServerRequestValidator(),
            $builder->getResponseValidator()
        );

        $this->expectException($expectedExceptionType);
        $middleware->process($serverRequest, $handler);
    }
}
