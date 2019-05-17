<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7\Validators;

use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\Exception\Request\Security\RequestSecurityMismatch;
use OpenAPIValidation\PSR7\ServerRequestValidator;
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

    public function testItChecksBearerHeaderGreen() : void
    {
        $request = (new ServerRequest('get', '/products'))
            ->withHeader('Authorization', 'Bearer ABCDEFG');

        $validator = ServerRequestValidator::fromYaml($this->specBearer);
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItChecksBearerHeaderRed() : void
    {
        $request = new ServerRequest('get', '/products');

        try {
            $validator = ServerRequestValidator::fromYaml($this->specBearer);
            $validator->validate($request);
            $this->fail('Expected exception');
        } catch (RequestSecurityMismatch $e) {
            $this->assertEquals('/products', $e->addr()->path());
            $this->assertEquals('get', $e->addr()->method());
        }
    }

    public function testItChecksBasicHeaderGreen() : void
    {
        $request = (new ServerRequest('get', '/products'))
            ->withHeader('Authorization', 'Basic ABCDEFG');

        $validator = ServerRequestValidator::fromYaml($this->specBasic);
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testItChecksBasicHeaderRed() : void
    {
        $request = new ServerRequest('get', '/products');

        try {
            $validator = ServerRequestValidator::fromYaml($this->specBasic);
            $validator->validate($request);
            $this->fail('Expected exception');
        } catch (RequestSecurityMismatch $e) {
            $this->assertEquals('/products', $e->addr()->path());
            $this->assertEquals('get', $e->addr()->method());
        }
    }
}
