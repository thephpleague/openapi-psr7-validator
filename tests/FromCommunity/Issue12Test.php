<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

use function json_encode;

final class Issue12Test extends TestCase
{
    /**
     * @see https://github.com/lezhnev74/openapi-psr7-validator/issues/12
     *
     * @param mixed[]|null $example
     *
     * @dataProvider getNullableTypeExamples
     */
    public function testIssue12(?array $example): void
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

        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();

        $psrRequest = (new ServerRequest('post', 'http://localhost:8000/api/v1/products.create'))
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Utils::streamFor(json_encode(['test' => $example])));

        $validator->validate($psrRequest);

        $this->addToAssertionCount(1);
    }

    /**
     * @return mixed[]
     */
    public function getNullableTypeExamples(): array
    {
        return [
            'nullable null' => [null],
            'nullable array' => [[123]],
        ];
    }
}
