<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

use function json_encode;

final class Issue32Test extends TestCase
{
    /**
     * @see https://github.com/thephpleague/openapi-psr7-validator/issues/32
     */
    public function testIssue32(): void
    {
        $yaml = /** @lang yaml */
            <<<'YAML'
openapi: 3.0.0
paths:
  /api/test/create:
    post:
      description: 'Test'
      operationId: api.prescription.create
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/prescription'
      responses:
        '200':
          description: 'ok'
components:
  schemas:
    prescription:
      properties:
        exampleTyp:
          type: string
          enum:
            - VALID
            - STILLVALID
YAML;

        $data = ['exampleTyp' => 'INVALID'];

        $psrRequest = (new ServerRequest('post', 'http://localhost:8000/api/test/create'))
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Utils::streamFor(json_encode($data)));

        $serverRequestValidator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();

        $this->expectException(ValidationFailed::class);
        $serverRequestValidator->validate($psrRequest);
    }
}
