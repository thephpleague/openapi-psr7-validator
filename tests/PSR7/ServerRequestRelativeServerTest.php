<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;

class ServerRequestRelativeServerTest extends BaseValidatorTest
{
    /**
     * @return array<array<ServerRequest>>
     */
    public function validDataProvider(): array
    {
        return [
            // matches server: https://localhost/v1
            [new ServerRequest('get', '/v1/products')],
            [new ServerRequest('get', 'http://anyhost/v1/products')],
            [new ServerRequest('get', 'https://localhost/v1/products')],

            // matches server: /v2
            [new ServerRequest('get', '/v2/products')],
            [new ServerRequest('get', 'http://anyhost/v2/products')],

            // matches overridden server: https://special.host/v3
            [new ServerRequest('get', '/v3/products/overridden')],
            [new ServerRequest('get', 'http://anyhost/v3/products/overridden')],

            // matches overridden server: /v4
            [new ServerRequest('get', '/v4/products/overridden')],
            [new ServerRequest('get', 'http://anyhost/v4/products/overridden')],
            [new ServerRequest('get', 'https://special.host/v4/products/overridden')],
        ];
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testItAllowsRelativesServerUrlsGreen(ServerRequest $request): void
    {
        $spec = <<<SPEC
openapi: "3.0.0"
info:
  title: Test API
  description: Testing servers keyword
  version: "1.0.0"
servers:
  - url: https://localhost/v1
  - url: /v2
paths:
  /products:
    get:
      summary: Inherits servers
  /products/overridden:
    servers:
      - url: https://special.host/v3
      - url: /v4
    get:
      summary: Overrides servers
SPEC;

        $validator = (new ValidatorBuilder())->fromYaml($spec)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }
}
