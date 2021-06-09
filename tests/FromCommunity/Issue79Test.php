<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use cebe\openapi\Reader;
use League\OpenAPIValidation\PSR7\PathFinder;
use PHPUnit\Framework\TestCase;

/**
 * @see https://github.com/thephpleague/openapi-psr7-validator/issues/79
 */
final class Issue79Test extends TestCase
{
    /**
     * @dataProvider provideSpecAndOperationToMatch()
     */
    public function testItFindsMatchingOperationWithTheRightServer(
        string $spec,
        string $path,
        string $method,
        string $expectedPath
    ): void {
        $pathFinder = new PathFinder(Reader::readFromYaml($spec), $path, $method);
        $opAddrs    = $pathFinder->search();

        $this->assertCount(1, $opAddrs);
        $this->assertEquals($expectedPath, $opAddrs[0]->path());
    }

    /**
     * @return iterable<string[]>
     */
    public function provideSpecAndOperationToMatch(): iterable
    {
        yield 'Server override on the operation level' => [
            <<<YAML
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
servers:
  - url: /v1
paths:
  /products/{id}:
    servers:
      - url: /v2
    get:
      summary: Product Types
      servers:
        - url: /v3
YAML
,
            '/v3/products/10',
            'get',
            '/products/{id}',
        ];

        yield 'Server override on the path level' => [
            <<<YAML
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
servers:
  - url: /v1
paths:
  /products/{id}:
    servers:
      - url: /v2
    get:
      summary: Product Types
YAML
,
            '/v2/products/10',
            'get',
            '/products/{id}',
        ];

        yield 'Server from the root level' => [
            <<<YAML
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
servers:
  - url: /v1
paths:
  /products/{id}:
    get:
      summary: Product Types
YAML
,
            '/v1/products/10',
            'get',
            '/products/{id}',
        ];

        yield 'Default server' => [
            <<<YAML
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
paths:
  /products/{id}:
    get:
      summary: Product Types
YAML
,
            '/products/10',
            'get',
            '/products/{id}',
        ];
    }

    /**
     * @dataProvider provideSpecAndOperationToNotMatch()
     */
    public function testItDoesNotFindMatchingOperationWithTheWrongServer(
        string $spec,
        string $path,
        string $method
    ): void {
        $pathFinder = new PathFinder(Reader::readFromYaml($spec), $path, $method);
        $opAddrs    = $pathFinder->search();

        $this->assertCount(0, $opAddrs);
    }

    /**
     * @return iterable<string[]>
     */
    public function provideSpecAndOperationToNotMatch(): iterable
    {
        yield 'Server override on the operation level' => [
            <<<YAML
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
servers:
  - url: /v1
paths:
  /products/{id}:
    servers:
      - url: /v2
    get:
      summary: Product Types
      servers:
        - url: /v3
YAML
,
            '/v2/products/10',
            'get',
        ];

        yield 'Server override on the path level' => [
            <<<YAML
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
servers:
  - url: /v1
paths:
  /products/{id}:
    servers:
      - url: /v2
    get:
      summary: Product Types
YAML
,
            '/v1/products/10',
            'get',
        ];

        yield 'Server from the root level' => [
            <<<YAML
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
servers:
  - url: /v1
paths:
  /products/{id}:
    get:
      summary: Product Types
YAML
,
            '/products/10',
            'get',
        ];
    }
}
