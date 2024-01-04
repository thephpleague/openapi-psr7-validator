<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

use function json_encode;

final class Issue57Test extends TestCase
{
    /**
     * @see https://github.com/lezhnev74/openapi-psr7-validator/issues/57
     */
    public function testIssue57(): void
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
                  type: object
                  properties:
                    some_property_here:
                      oneOf:
                        - type: object
                        - type: string
                          maxLength: 50
                          minLength: 1
                  
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

        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();

        $psrRequest = (new ServerRequest('post', 'http://localhost:8000/api/v1/products.create'))
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Utils::streamFor(json_encode(['test' => (object) ['some_property_here' => (object) []]])));

        $validator->validate($psrRequest);

        $this->addToAssertionCount(1);
    }
}
