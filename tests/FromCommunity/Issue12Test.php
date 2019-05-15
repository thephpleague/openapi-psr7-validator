<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;
use function json_encode;

final class Issue12Test extends TestCase
{
    /**
     * https://github.com/lezhnev74/openapi-psr7-validator/issues/12
     *
     * @param $example
     *
     * @dataProvider getNullableTypeExamples
     */
    public function test_issue12($example) : void
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
              properties:
                test:
                  nullable: true
                  type: array
                  items:
                    type: integer
                  minItems: 1
                  
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

        $validator = ServerRequestValidator::fromYaml($yaml);

        $psrRequest = (new ServerRequest('post', 'http://localhost:8000/api/v1/products.create'))
            ->withHeader('Content-Type', 'application/json')
            ->withBody(stream_for(json_encode(['test' => $example])));

        $validator->validate($psrRequest);

        $this->addToAssertionCount(1);
    }

    public function getNullableTypeExamples() : array
    {
        return [
            'nullable null' => [null],
            'nullable array' => [[123]],
        ];
    }
}
