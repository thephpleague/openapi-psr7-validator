<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Tests\PSR7\BaseValidatorTest;

use function parse_str;

/**
 * @see https://github.com/thephpleague/openapi-psr7-validator/issues/140
 */
final class Issue140Test extends BaseValidatorTest
{
    public function testIssue140(): void
    {
        $json = /** @lang json */
               <<<JSON
{
    "openapi": "3.0.0",
    "servers": [
        {
            "url": "https://localhost"
        }
    ],
    "paths": {
        "/api/list": {
            "get": {
                "summary": "Get a list filtred by ids",
                "parameters": [
                    {
                        "name": "id",
                        "in": "query",
                        "description": "Array of ids",
                        "required": true,
                        "schema": {
                            "type": "array",
                            "items": {
                                "type": "number"
                            }
                        }
                    }
                ],
                "responses": {}
            }
        }
    }
}
JSON;

        $validator = (new ValidatorBuilder())->fromJson($json)->getServerRequestValidator();

        $queryString = 'id[]=1&id[]=2';
        $query       = null;
        parse_str($queryString, $query);

        $psrRequest = (new ServerRequest('get', 'http://localhost:8000/api/list'))
            ->withHeader('Content-Type', 'application/json')
            ->withQueryParams($query);

        $validator->validate($psrRequest);

        $this->addToAssertionCount(1);
    }
}
