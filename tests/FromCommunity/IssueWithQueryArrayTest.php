<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class IssueWithQueryArrayTest extends TestCase
{
    public function testConvertFormIntegerArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'integer', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertFormNumberArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'number', 'float'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'number'));
        $this->addToAssertionCount(1);
    }

    public function testConvertFormIntegerArrayToStringArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'string', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertFormStringArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'string', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'string'));
        $this->addToAssertionCount(1);
    }

    public function testConvertFormBooleanArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'boolean', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'boolean'));
        $this->addToAssertionCount(1);
    }

    public function testConvertFormIntegerArrayError() : void
    {
        $this->expectExceptionMessage('Value "id1,id2,id3" for argument "id" is invalid for Request [get /users]');
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'integer', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'string'));
        $this->addToAssertionCount(1);
    }

    public function testConvertSpaceIntegerArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('spaceDelimited', 'integer', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('spaceDelimited', 'integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertPipeIntegerArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('pipeDelimited', 'integer', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('pipeDelimited', 'integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertDeepObject() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeDeepObjectYaml())->getServerRequestValidator();
        $validator->validate($this->makeRequest('deepObject', 'integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertDeepObjectError() : void
    {
        $this->expectExceptionMessage('Value "{
    "before": "ten",
    "after": "one"
}" for argument "id" is invalid for Request [get /users]');
        $validator = (new ValidatorBuilder())->fromYaml($this->makeDeepObjectYaml())->getServerRequestValidator();
        $validator->validate($this->makeRequest('deepObject', 'error'));
        $this->addToAssertionCount(1);
    }

    protected function makeArrayYaml(string $style, string $type, string $format) : string
    {
        return $yaml = /** @lang yaml */
            <<<YAML
openapi: 3.0.0
info:
  title: Product import API
  version: '1.0'
servers:
  - url: 'http://localhost:8000/api/v1'
paths:
  /users:
    get:
      parameters:
        - in: query
          name: id
          required: true
          style: $style
          explode: false
          schema:
            type: array
            items:
              type: $type
              format: $format
      responses:
        '200':
          description: A list of users
YAML;
    }

    protected function makeDeepObjectYaml() : string
    {
        return $yaml = /** @lang yaml */
            <<<YAML
openapi: 3.0.0
info:
  title: Product import API
  version: '1.0'
servers:
  - url: 'http://localhost:8000/api/v1'
paths:
  /users:
    get:
      parameters:
        - in: query
          name: id
          required: true
          style: deepObject
          explode: true
          schema:
            type: object
            properties:
              before:
                type: integer
                format: int32
              after:
                type: integer
                format: int32
      responses:
        '200':
          description: A list of users
YAML;
    }

    protected function makeRequest(string $style, string $type) : ServerRequest
    {
        $map     = [
            'form' => ['integer' => '1,2,3', 'string' => 'id1,id2,id3', 'boolean' => 'true,false', 'number' => '1.00,2.00,3.00'],
            'spaceDelimited' => ['integer' => '1 2 3', 'string' => 'id1 id2 id3', 'boolean' => 'true false', 'number' => '1.00 2.00 3.00'],
            'pipeDelimited' => ['integer' => '1|2|3', 'string' => 'id1|id2|id3', 'boolean' => 'true|false', 'number' => '1.00|2.00|3.00'],
            'deepObject' => ['integer' => ['before' => 10, 'after' => 1], 'string' => ['before' => '10', 'after' => '1'], 'error' => ['before' => 'ten', 'after' => 'one']],
        ];
        $request = new ServerRequest('GET', 'http://localhost:8000/api/v1/users');
        $request = $request->withQueryParams(['id' => $map[$style][$type]]);

        return $request;
    }
}
