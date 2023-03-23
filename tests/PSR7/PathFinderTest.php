<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use cebe\openapi\Reader;
use League\OpenAPIValidation\PSR7\PathFinder;
use PHPUnit\Framework\TestCase;

final class PathFinderTest extends TestCase
{
    public function testItFindsMatchingOperation(): void
    {
        $spec = <<<SPEC
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
  /products/{review}:
    post:
      summary: Product Types
SPEC;

        $pathFinder = new PathFinder(Reader::readFromYaml($spec), '/v1/products/10', 'get');
        $opAddrs    = $pathFinder->search();

        $this->assertCount(1, $opAddrs);
        $this->assertEquals('/products/{id}', $opAddrs[0]->path());
    }

    public function testItFindsMatchingOperationWithParametrizedServer(): void
    {
        $spec = <<<SPEC
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
servers:
  - url: /v1/{date}
paths:
  /products/{id}:
    get:
      summary: Product Types
  /products/{review}:
    post:
      summary: Product Types
SPEC;

        $pathFinder = new PathFinder(Reader::readFromYaml($spec), '/v1/2019-05-07/products/20', 'get');
        $opAddrs    = $pathFinder->search();

        $this->assertCount(1, $opAddrs);
        $this->assertEquals('/products/{id}', $opAddrs[0]->path());
    }

    public function testItFindsMatchingOperationForFullUrl(): void
    {
        $spec = <<<SPEC
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
servers:
  - url: https://localhost/v1
  - url: /v1.2
paths:
  /products/{id}:
    get:
      summary: Product Types
  /products/{review}:
    post:
      summary: Product Types
SPEC;

        $pathFinder = new PathFinder(Reader::readFromYaml($spec), 'https://localhost/v1/products/10', 'get');
        $opAddrs    = $pathFinder->search();

        $this->assertCount(1, $opAddrs);
        $this->assertEquals('/products/{id}', $opAddrs[0]->path());
    }

    public function testItFindsMatchingOperationForMultipleServersWithSamePath(): void
    {
        $spec = <<<SPEC
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
servers:
  - url: https://localhost/v1
  - url: https://staging.example.com/v1
  - url: https://prod.example.com/v1
paths:
  /products/{id}:
    get:
      summary: Product Types
  /products/{review}:
    post:
      summary: Product Types
SPEC;

        $pathFinder = new PathFinder(Reader::readFromYaml($spec), 'https://localhost/v1/products/10', 'get');
        $opAddrs    = $pathFinder->search();

        $this->assertCount(1, $opAddrs);
        $this->assertEquals('/products/{id}', $opAddrs[0]->path());
    }

    public function testItPrioritisesOperatorsThatAreMoreStatic(): void
    {
        $spec = <<<SPEC
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
paths:
  /products/{product}/images/{image}:
    get:
      summary: A specific image for a specific product
  /products/{product}/images/thumbnails:
    get:
      summary: All thumbnail images for a specific product
SPEC;

        $pathFinder = new PathFinder(Reader::readFromYaml($spec), '/products/10/images/thumbnails', 'get');
        $opAddrs    = $pathFinder->search();

        $this->assertCount(1, $opAddrs);
        $this->assertEquals('/products/{product}/images/thumbnails', $opAddrs[0]->path());
    }

    public function testItPrioritises2EquallyDynamicPaths(): void
    {
        $spec = <<<SPEC
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
paths:
  /products/{product}/images/{image}/{size}:
    get:
      summary: A specific image for a specific product
  /products/{product}/images/thumbnails/{size}:
    get:
      summary: All thumbnail images for a specific product
  /products/{product}/images/{image}/primary:
    get:
      summary: All thumbnail images for a specific product
SPEC;

        $pathFinder = new PathFinder(Reader::readFromYaml($spec), '/products/10/images/thumbnails/primary', 'get');
        $opAddrs    = $pathFinder->search();

        $this->assertCount(2, $opAddrs);
        $this->assertEquals('/products/{product}/images/thumbnails/{size}', $opAddrs[0]->path());
        $this->assertEquals('/products/{product}/images/{image}/primary', $opAddrs[1]->path());
    }
}
