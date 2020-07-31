<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

final class IssueWithQueryArrayTest extends TestCase
{
    public function testConvertIntegerArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeYaml('integer', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertNumberArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeYaml('number', 'float'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('number'));
        $this->addToAssertionCount(1);
    }

    public function testConvertIntegerArrayToStringArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeYaml('string', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertStringArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeYaml('string', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('string'));
        $this->addToAssertionCount(1);
    }

    public function testConvertBooleanArray() : void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeYaml('boolean', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('boolean'));
        $this->addToAssertionCount(1);
    }

    public function testConvertIntegerArrayError() : void
    {
        $this->expectExceptionMessage('Value "id1,id2,id3" for argument "id" is invalid for Request [get /users]');
        $validator = (new ValidatorBuilder())->fromYaml($this->makeYaml('integer', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('string'));
        $this->addToAssertionCount(1);
    }

    protected function makeYaml(string $type, string $format): string
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
          style: form
          explode: false
          schema:
            type: array
            items:
              type: $type
              format: $format
YAML;
    }

    protected function makeRequest(string $type) : ServerRequest
    {
        $map = [
            'integer' => '1,2,3',
            'string' => 'id1,id2,id3',
            'boolean' => 'true,false',
            'number' => '1.00,2.00,3.00',
        ];
        $request = new ServerRequest('GET', 'http://localhost:8000/api/v1/users');
        $request->withQueryParams(['id' => $map[$type]]);

        return $request;
    }
}