<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\ValidatorBuilder;

class ServerRequestRelativeServer extends BaseValidatorTest
{
    public function testItAllowsRelativesServerUrlsGreen() : void
    {
        $spec = <<<SPEC
openapi: "3.0.0"
info:
  title: Uber API
  description: Move your app forward with the Uber API
  version: "1.0.0"
servers:
  - url: https://localhost/v1
  - url: /v2
paths:
  /products:
    get:
      summary: Product Types
SPEC;

        $request   = new ServerRequest('get', 'https://localhost/v1/products');
        $validator = (new ValidatorBuilder())->fromYaml($spec)
                                             ->setServerUri('https://localhost')
                                             ->getServiceRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }
}
