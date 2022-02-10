<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\Response;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Tests\PSR7\BaseValidatorTest;

final class NullableSchemaTest extends BaseValidatorTest
{
    public function testNullableImplicitResult(): void
    {
        $yaml = /** @lang yaml */
            <<<'YAML'
openapi: 3.0.0
paths:
  /api/nullable:
    get:
      description: 'Test'
      responses:
        '200':
          description: 'ok'
          content:
            application/json:
              schema:
                $ref: "#/components/schemas/Thing"
components:
  schemas:
    FooResult:
      properties:
        id:
          type: integer
        foo:
          type: string
    Thing:
      type: object
      properties:
        result:
          schema:
            - $ref: "#/components/schemas/FooResult"
YAML;

        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getResponseValidator();
        $operation = new OperationAddress('/api/nullable', 'get');

        $responseContent = /** @lang JSON */
            '
{
  "result": null
}
';

        $response = new Response(200, ['Content-Type' => 'application/json'], $responseContent);

        $validator->validate($operation, $response);

        $this->addToAssertionCount(1);
    }
}
