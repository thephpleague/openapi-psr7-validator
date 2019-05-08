<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 07 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidationTests\FromCommunity;


use cebe\openapi\Reader;
use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use PHPUnit\Framework\TestCase;
use function GuzzleHttp\Psr7\stream_for;

class ScayTraseTest extends TestCase
{
    # https://github.com/lezhnev74/openapi-psr7-validator/issues/3
    function test_issue3()
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
                  type: integer
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

        $validator = new ServerRequestValidator(Reader::readFromYaml($yaml));

        $psrRequest = (new ServerRequest('post', 'http://localhost:8000/api/v1/products.create'))
            ->withHeader('Content-Type', 'application/json')
            ->withBody(stream_for(json_encode(['test' => 20])));

        $validator->validate($psrRequest);

        $this->addToAssertionCount(1);
    }
}