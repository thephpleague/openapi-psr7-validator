<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\Response;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class RouteCollisionIssueTest extends TestCase
{
    public function testRouteCollisionIsHandlerProperly(): void
    {
        $schema = /** @lang JSON */
            '
  {
    "openapi": "3.0.0",
    "info": {
      "description": "",
      "version": "1.0.0",
      "title": "Apples"
    },
    "servers": [
      {
        "url": "/api"
      }
    ],
    "paths": {
      "/apples/{apple_id}": {
        "get": {
          "summary": "",
          "description": "",
          "operationId": "appleGet",
          "parameters": [
            {
              "in": "path",
              "name": "apple_id",
              "schema": {
                "type": "integer"
              },
              "required": true
            }
          ],
          "responses": {
            "200": {
              "description": "Success",
              "content": {
                "application/json": {
                  "schema": {
                    "type": "object",
                    "required": [
                      "apple"
                    ],
                    "properties": {
                      "apple": {
                        "$ref": "#/components/schemas/apple"
                      }
                    }
                  }
                }
              }
            }
          }
        }
      },
      "/apples/ready-to-eat": {
        "get": {
          "summary": "",
          "description": "",
          "operationId": "applesReadyGet",
          "responses": {
            "200": {
              "description": "Success",
              "content": {
                "application/json": {
                  "schema": {
                    "properties": {
                      "apples": {
                        "type": "array",
                        "items": {
                          "$ref": "#/components/schemas/apple"
                        }
                      }
                    },
                    "required": [
                      "apples"
                    ]
                  }
                }
              }
            }
          }
        }
      }
    },
    "components": {
      "schemas": {
        "apple": {
          "type": "object",
          "required": [
            "id"
          ],
          "properties": {
            "id": {
              "type": "integer"
            }
          }
        }
      }
    }
  }
';

        $validator = (new ValidatorBuilder())->fromJson($schema)->getResponseValidator();
        $operation = new OperationAddress('/api/apples/ready-to-eat', 'get');

        $responseContent = /** @lang JSON */
            '
    {
      "apples": [
        {
          "id": 0
        }
      ]
    }
';

        $response = new Response(200, ['Content-Type' => 'application/json'], $responseContent);

        $validator->validate($operation, $response);

        $this->addToAssertionCount(1);
    }
}
