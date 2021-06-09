<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use cebe\openapi\Reader;
use PHPUnit\Framework\TestCase;

final class HeaderParametersReferenceResolution extends TestCase
{
    public function testHeaderParametersWithReference(): void
    {
        $yaml = <<<'YAML'
openapi: 3.0.0
info:
  title: Product import API
  version: '1.0'
servers:
  - url: 'http://localhost:8000/api/v1'
paths:
  /products.create:
    post:
      parameters:
        - $ref: '#/components/parameters/appIdHeader'
      requestBody:
        required: true
        content:
          application/json:
            schema:
              properties:
                url:
                  type: string
      responses:
        '200':
          description: OK
          content:
            application/json:
              schema:
                properties:
                  result: 
                    type: string
components:
  parameters:
    appIdHeader:
      schema:
        type: integer
      in: header
      required: true
      name: X-APP-ID
      description: App id used to identify request.
YAML;

        $schema = Reader::readFromYaml($yaml);

        $operation = $schema->paths->getPath('/products.create')->getOperations()['post'];

        foreach ($operation->parameters as $parameter) {
            if ($parameter->in !== 'header') {
                continue;
            }

            $this->addToAssertionCount(1);
        }
    }
}
