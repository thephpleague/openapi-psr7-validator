<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7\Validators;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidSecurity;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class SecurityHTTPTest extends TestCase
{
    /** @var string */
    private $specBearer = <<<BEARER
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
paths:
  /products:
    get:
      summary: Product Types
      description: The Products endpoint returns information about the Uber products offered at a given location. The response includes the display name and other details about each product, and lists the products in the proper display order.
      security:
        - bearerHttp: []
components:
  securitySchemes:
    bearerHttp:
      type: http
      scheme: bearer
BEARER;

    /** @var string */
    private $specBasic = <<<BASIC
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
paths:
  /products:
    get:
      summary: Product Types
      description: The Products endpoint returns information about the Uber products offered at a given location. The response includes the display name and other details about each product, and lists the products in the proper display order.
      security:
        - bearerHttp: []
components:
  securitySchemes:
    bearerHttp:
      type: http
      scheme: basic
BASIC;

    public function testItChecksBearerHeaderGreen(): void
    {
        $request = (new ServerRequest('get', '/products'))
            ->withHeader('Authorization', 'Bearer ABCDEFG');

        $validator = (new ValidatorBuilder())->fromYaml($this->specBearer)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItChecksBearerHeaderRed(): void
    {
        $request = new ServerRequest('get', '/products');

        $this->expectException(InvalidSecurity::class);
        $this->expectExceptionMessage('None of security schemas did match for Request [get /products]');

        $validator = (new ValidatorBuilder())->fromYaml($this->specBearer)->getServerRequestValidator();
        $validator->validate($request);
    }

    public function testItChecksBasicHeaderGreen(): void
    {
        $request = (new ServerRequest('get', '/products'))
            ->withHeader('Authorization', 'Basic ABCDEFG');

        $validator = (new ValidatorBuilder())->fromYaml($this->specBasic)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItChecksBasicHeaderRed(): void
    {
        $request = new ServerRequest('get', '/products');

        $this->expectException(InvalidSecurity::class);
        $this->expectExceptionMessage('None of security schemas did match for Request [get /products]');

        $validator = (new ValidatorBuilder())->fromYaml($this->specBasic)->getServerRequestValidator();
        $validator->validate($request);
    }
}
