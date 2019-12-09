<?php declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7\Validators;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class PathValidatorTest extends TestCase
{
    public function testItThrowsHelpfulExceptionWhenMissingParams(): void
    {
        $this->expectException(ValidationFailed::class);

        $json      = /** @lang JSON */
            <<<'JSON'
{
    "openapi": "3.0.0",
    "info": {
        "title": "API",
        "version": "1.0"
    },
    "paths": {
        "/api/1.0/order/{orderId}": {
            "get": {
                "operationId": "get_order"
                }
        }
    }
}
JSON;
        $validator = (new ValidatorBuilder())->fromJson($json)->getRequestValidator();
        $validator->validate(new Request('get', new Uri('/api/1.0/order/123')));

    }
}
