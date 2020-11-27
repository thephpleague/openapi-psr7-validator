<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use PHPUnit\Framework\TestCase;

class PathParsingTest extends TestCase
{
    public function testInvalidPathParams(): void
    {
        // that specification doesn't raise any errors in swagger-editor
        $yaml      = /** @lang yaml */
            <<<YAML
openapi: 3.0.0
info:
  title: Product import API
  version: '1.0'
servers:
  - url: 'http://localhost:8000/api/v1'
paths:
  /test/{invalid{brackets}:
    parameters: 
      - name: 'invalid{brackets'
        in: path
        schema:
          type: string
        required: true
    get:
      responses:
        '204':
          description: no data
YAML;
        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();

        $psrRequest = (new ServerRequest('get', 'http://localhost:8000/api/v1/test/whatever'));

        $this->expectException(InvalidSchema::class);
        $validator->validate($psrRequest);
    }
}
