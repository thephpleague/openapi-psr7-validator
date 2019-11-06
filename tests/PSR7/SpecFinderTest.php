<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use League\OpenAPIValidation\PSR7\CallbackAddress;
use League\OpenAPIValidation\PSR7\SpecFinder;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
use function array_keys;
use function iterator_to_array;

final class SpecFinderTest extends TestCase
{
    public function testFindCallbackSpecs() : void
    {
        $yaml = <<<YAML
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
                url:
                  type: string
      callbacks:
        productCreated:
          '{\$request.body#/url}':
            post:
              requestBody:
                content:
                  application/json:
                    schema:
                      properties:
                        success:
                          type: boolean
              responses:
                '200':
                  description: Callback received the request.
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

        $schema     = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator()->getSchema();
        $specFinder = new SpecFinder($schema);

        $address   = new CallbackAddress('/products.create', 'post', 'productCreated', 'post');
        $operation = $specFinder->findOperationSpec($address);

        // Some assertions to ensure we have the right operation
        $this->assertEquals('boolean', $operation->requestBody->content['application/json']->schema->properties['success']->type);
        $this->assertEquals(['200'], array_keys(iterator_to_array($operation->responses->getIterator())));
    }
}
