<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\Request;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Tests\PSR7\BaseValidatorTest;

use function json_encode;

final class IssueNullablePropertyTest extends BaseValidatorTest
{
    public function testNullableFalseThrowInvalidBody(): void
    {
        $json = /** @lang json */
        <<<JSON
{
  "openapi": "3.0.0",
  "paths": {
    "/api/data": {
      "post": {
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "type": "object",
                "required": [
                  "value"
                ],
                "properties": {
                  "value": {
                    "nullable": false,
                    "oneOf": [
                      {"type": "string"},
                      {"type": "boolean"}
                    ]
                  }
                }
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "A response",
            "content": {
              "application/json": {
                "schema": {
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
JSON;

        $requestValidator = (new ValidatorBuilder())
            ->fromJson($json)
            ->getRequestValidator();

        $request = new Request(
            'POST',
            'headers:/api/data',
            ['Content-Type' => 'application/json'],
            json_encode(['value' => null])
        );

        $this->expectException(InvalidBody::class);

        $requestValidator->validate($request);
    }
}
