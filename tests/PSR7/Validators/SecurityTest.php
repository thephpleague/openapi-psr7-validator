<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 07 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7\Validators;

use cebe\openapi\Reader;
use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\Exception\Request\Security\RequestSecurityMismatch;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    // Security schemes united as AND
    protected $specSecurityORUnion = <<<OR
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
        - apikey1: []
        - apikey2: []
        - apikey3: []
components:
  securitySchemes:
    apikey1:
      type: apiKey
      name: server_token1
      in: query
    apikey2:
      type: apiKey
      name: server_token2
      in: header
    apikey3:
      type: apiKey
      name: server_token3
      in: cookie
OR;

    // Security schemes united as OR
    protected $specSecurityANDUnion = <<<AND
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
        - apikey1: []
          apikey2: []
components:
  securitySchemes:
    apikey1:
      type: apiKey
      name: server_token1
      in: query
    apikey2:
      type: apiKey
      name: server_token2
      in: header
AND;

    // Security schemes united as OR
    protected $specSecurityAND_OR_COMBINED = <<<AND
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
        - apikey1: []
          apikey2: []
        - apikey3: []
components:
  securitySchemes:
    apikey1:
      type: apiKey
      name: server_token1
      in: query
    apikey2:
      type: apiKey
      name: server_token2
      in: header
    apikey3:
      type: apiKey
      name: server_token3
      in: cookie
AND;


    function test_it_applies_security_rules_OR_green()
    {
        $request = (new ServerRequest("get", "/products"))->withQueryParams(['server_token1' => 'key value']);

        $validator = new ServerRequestValidator(Reader::readFromYaml($this->specSecurityORUnion));
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    function test_it_validates_missed_apiKey_red()
    {
        $request = (new ServerRequest("get", "/products"))->withQueryParams(['wrongToken' => 'key value']);

        try {
            $validator = new ServerRequestValidator(Reader::readFromYaml($this->specSecurityORUnion));
            $validator->validate($request);
            $this->fail("Expected exception");
        } catch (RequestSecurityMismatch $e) {
            $this->assertEquals('/products', $e->addr()->path());
            $this->assertEquals('get', $e->addr()->method());
        }
    }

    function test_it_applies_security_rules_AND_green()
    {
        $request = (new ServerRequest("get", "/products"))
            ->withQueryParams(['server_token1' => 'key value'])
            ->withHeader('server_token2', 'key value');

        $validator = new ServerRequestValidator(Reader::readFromYaml($this->specSecurityANDUnion));
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    function test_it_applies_security_rules_AND_red()
    {
        # request has no security header
        $request = (new ServerRequest("get", "/products"))
            ->withQueryParams(['server_token1' => 'key value']);

        try {
            $validator = new ServerRequestValidator(Reader::readFromYaml($this->specSecurityANDUnion));
            $validator->validate($request);
            $this->fail("Expected exception");
        } catch (RequestSecurityMismatch $e) {
            $this->assertEquals('/products', $e->addr()->path());
            $this->assertEquals('get', $e->addr()->method());
        }
    }

    function test_it_applies_security_rules_AND_OR_combined_green()
    {
        # request has one of allowed security cookies
        $request = (new ServerRequest("get", "/products"))
            ->withCookieParams(['server_token3' => 'key value']);

        $validator = new ServerRequestValidator(Reader::readFromYaml($this->specSecurityAND_OR_COMBINED));
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    function test_it_applies_security_rules_AND_OR_combined_red()
    {
        # request has one security query argument, but misses the second one (required one)
        $request = (new ServerRequest("get", "/products"))
            ->withQueryParams(['server_token1' => 'key value']);

        try {
            $validator = new ServerRequestValidator(Reader::readFromYaml($this->specSecurityAND_OR_COMBINED));
            $validator->validate($request);
            $this->fail("Expected exception");
        } catch (RequestSecurityMismatch $e) {
            $this->assertEquals('/products', $e->addr()->path());
            $this->assertEquals('get', $e->addr()->method());
        }
    }

}
