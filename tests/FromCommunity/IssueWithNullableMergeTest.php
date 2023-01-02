<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\Response;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Tests\PSR7\BaseValidatorTest;

final class IssueWithNullableMergeTest extends BaseValidatorTest
{
    public function testNullableMergeOneOf(): void
    {
        $yaml = /** @lang yaml */
            <<<'YAML'
openapi: 3.0.0
paths:
  /api/nullable-merge:
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
      type: object
      properties:
        id:
          type: integer
        foo:
          type: string
    BarResult:
      type: object
      nullable: true
      properties:
        id:
          type: integer
        bar:
          type: string
    Thing:
      type: object
      properties:
        result:
          oneOf:
            - $ref: "#/components/schemas/FooResult"
            - $ref: "#/components/schemas/BarResult"
YAML;

        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getResponseValidator();
        $operation = new OperationAddress('/api/nullable-merge', 'get');

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
