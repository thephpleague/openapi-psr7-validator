<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class Issue17Test extends TestCase
{
    /**
     * @see https://github.com/lezhnev74/openapi-psr7-validator/issues/17
     */
    public function testIssue17(): void
    {
        $yaml = /** @lang yaml */
            <<<YAML
openapi: 3.0.0
info:
  title: Product import API
  version: '1.0'
servers:
  - url: 'http://localhost:8000/api/v1'
paths:
  /products.create:
    post:
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              additionalProperties:
                type: string
      responses:
        '200':
          description: OK
          content:
            application/json:
              schema:
                properties:
                  result: 
                    type: string
YAML;

        $validator  = (new ValidatorBuilder())->fromYaml($yaml)->getRoutedRequestValidator();
        $psrRequest = new ServerRequest(
            'POST',
            'http://localhost:8000/api/v1/products.create',
            ['Content-Type' => 'application/json'],
            <<<JSON
{
    "stringOne":"foo",
    "stringTwo":"bar",
    "oneObject":{
        "more":"things"
    }
}
JSON
        );

        $address = new OperationAddress('/products.create', 'post');

        $this->expectException(ValidationFailed::class);

        $validator->validate($address, $psrRequest);
    }
}
