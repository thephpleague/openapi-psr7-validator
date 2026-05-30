<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

class OptionalRequestBodyTest extends TestCase
{
    public function testOptionalEmptyBodyIsValid(): void
    {
        $yaml = /** @lang yaml */
            <<<'YAML'
openapi: 3.0.0
info:
  title: Test API
  version: '1.0'
servers:
  - url: 'http://localhost:8000'
paths:
  /api:
    post:
      requestBody:
        content:
          application/json:
            schema:
              properties:
                name:
                  type: string
      responses:
        '204':
          description: No content
YAML;

        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();

        $validator->validate(
            new ServerRequest(
                'POST',
                '/api'
            )
        );

        $this->addToAssertionCount(1);
    }

    public function testRequiredEmptyBodyThrowsException(): void
    {
        $yaml = /** @lang yaml */
            <<<'YAML'
openapi: 3.0.0
info:
  title: Test API
  version: '1.0'
servers:
  - url: 'http://localhost:8000'
paths:
  /api:
    post:
      requestBody:
        required: true
        content:
          application/json:
            schema:
              properties:
                name:
                  type: string
      responses:
        '204':
          description: No content
YAML;

        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();

        $this->expectException(InvalidBody::class);
        $this->expectExceptionMessage('Required body is missing for Request [post /api]');
        $validator->validate(
            new ServerRequest(
                'POST',
                '/api'
            )
        );
    }
}
