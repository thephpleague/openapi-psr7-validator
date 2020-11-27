<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidQueryArgs;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class Issue52Test extends TestCase
{
    /**
     * @see https://github.com/thephpleague/openapi-psr7-validator/issues/52
     */
    public function testIssue52(): void
    {
        $json = /** @lang json */
            <<<JSON
{
  "openapi": "3.0.0",
  "info": {
    "title": "Product import API",
    "version": "1.0"
  },
  "servers": [
    {
      "url": "http://localhost:8000/api/v1"
    }
  ],
  "paths": {
    "/products": {
      "post": {
        "parameters":[
          {
            "name": "fields",
            "required":true,
            "in": "query",
            "schema": {
                "type": "array",
                "items": {
                  "type": "string",
                  "enum": [
                    "array"
                  ]
                }
                }
          }
        ],
        "responses": {
          "200": {
            "description": "OK",
            "content": {
              "application/json": {
                "schema": {
                  "properties": {
                    "result": {
                      "type": "string"
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
JSON;

        $validator = (new ValidatorBuilder())->fromJson($json)->getServerRequestValidator();

        $psrRequest = (new ServerRequest('post', 'http://localhost:8000/api/v1/products'))
            ->withHeader('Content-Type', 'application/json')
            ->withQueryParams([
                'fields' => ['array1'],
            ]);
        $this->expectException(InvalidQueryArgs::class);
        $validator->validate($psrRequest);
    }
}
