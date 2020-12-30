<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidParameter;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidQueryArgs;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use League\OpenAPIValidation\Schema\Exception\TypeMismatch;
use PHPUnit\Framework\TestCase;

final class IssueWithQueryArrayTest extends TestCase
{
    public function testConvertFormIntegerArray(): void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'integer', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertFormNumberArray(): void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'number', 'float'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'number'));
        $this->addToAssertionCount(1);
    }

    public function testConvertFormIntegerArrayToStringArray(): void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'string', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertFormStringArray(): void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'string', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'string'));
        $this->addToAssertionCount(1);
    }

    public function testConvertFormBooleanArray(): void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'boolean', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'boolean'));
        $this->addToAssertionCount(1);
    }

    public function testConvertFormIntegerArrayError(): void
    {
        $this->expectExceptionMessage('Value "id1,id2,id3" for argument "id" is invalid for Request [get /users]');
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('form', 'integer', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('form', 'string'));
    }

    public function testConvertSpaceIntegerArray(): void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('spaceDelimited', 'integer', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('spaceDelimited', 'integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertPipeIntegerArray(): void
    {
        $validator = (new ValidatorBuilder())->fromYaml($this->makeArrayYaml('pipeDelimited', 'integer', 'int32'))->getServerRequestValidator();
        $validator->validate($this->makeRequest('pipeDelimited', 'integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertSingleLayerDeepObject(): void
    {
        $yaml      = /** @lang yaml */
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
        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
        $validator->validate($this->makeRequest('deepObject', 'integer'));
        $this->addToAssertionCount(1);
    }

    public function testConvertSingleLayerDeepObjectError(): void
    {
        $yaml = /** @lang yaml */
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
        try {
            $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
            $validator->validate($this->makeRequest('deepObject', 'error'));
        } catch (InvalidQueryArgs $exception) {
            /** @var InvalidParameter $previous */
            $previous = $exception->getPrevious();
            /** @var TypeMismatch $previous */
            $previous = $previous->getPrevious();
            self::assertInstanceOf(TypeMismatch::class, $previous);
            self::assertEquals(['id', 'before'], $previous->dataBreadCrumb()->buildChain());
        }
    }

    public function testConvertMultiLayerDeepObject(): void
    {
        $yaml      = /** @lang yaml */
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
                type: object
                properties:
                  first:
                    type: object
                    properties:
                      second:
                        type: integer
                        format: int32
              after:
                type: integer
                format: int32
      responses:
        '200':
          description: A list of users
YAML;
        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
        $validator->validate($this->makeRequest('deepObject', 'deep'));
        $this->addToAssertionCount(1);
    }

    public function testConvertNumericKeysDeepObject(): void
    {
        $yaml      = /** @lang yaml */
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
            additionalProperties:
              type: integer
      responses:
        '200':
          description: A list of users
YAML;
        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
        $request   = $this->makeRequest('deepObject', 'numericKeys');

        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testConvertDeepArrayInteger(): void
    {
        $yaml      = /** @lang yaml */
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
            type: array
            items:
              type: integer
      responses:
        '200':
          description: A list of users
YAML;
        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
        $request   = $this->makeRequest('deepObject', 'deepArrayInteger');

        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    /**
     * DeepObject without explode should works too
     */
    public function testConvertDeepArrayIntegerWithoutExplode(): void
    {
        $yaml      = /** @lang yaml */
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
          schema:
            type: array
            items:
              type: integer
      responses:
        '200':
          description: A list of users
YAML;
        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
        $request   = $this->makeRequest('deepObject', 'deepArrayInteger');

        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testConvertDeepArrayBoolean(): void
    {
        $yaml      = /** @lang yaml */
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
            type: array
            items:
              type: boolean
      responses:
        '200':
          description: A list of users
YAML;
        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
        $request   = $this->makeRequest('deepObject', 'deepArrayBoolean');

        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testConvertDeepArrayString(): void
    {
        $yaml      = /** @lang yaml */
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
            type: array
            items:
              type: string
      responses:
        '200':
          description: A list of users
YAML;
        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
        $request   = $this->makeRequest('deepObject', 'deepArrayStrings');

        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testConvertDeepArrayOfArrayInteger(): void
    {
        $yaml      = /** @lang yaml */
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
            type: array
            items:
              type: array
              items:
                type: integer
      responses:
        '200':
          description: A list of users
YAML;
        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
        $request   = $this->makeRequest('deepObject', 'deepArrayOfArrayInteger');

        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testConvertDeepArrayIntegerNegative(): void
    {
        // there should be ints instead of strings in the array
        $this->expectException(InvalidQueryArgs::class);
        $yaml      = /** @lang yaml */
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
            type: array
            items:
              type: boolean
      responses:
        '200':
          description: A list of users
YAML;
        $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
        try {
            $validator->validate($this->makeRequest('deepObject', 'deepArrayStrings'));
        } catch (InvalidQueryArgs $exception) {
            /** @var InvalidParameter $previous */
            $previous = $exception->getPrevious();
            /** @var TypeMismatch $previous */
            $previous = $previous->getPrevious();
            self::assertEquals(['id', 0], $previous->dataBreadCrumb()->buildChain());

            throw $exception;
        }
    }

    public function testConvertMultiLayerDeepObjectError(): void
    {
        $yaml = /** @lang yaml */
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
                type: object
                properties:
                  first:
                    type: object
                    properties:
                      second:
                        type: string
                        format: date-time
              after:
                type: integer
                format: int32
      responses:
        '200':
          description: A list of users
YAML;
        try {
            $validator = (new ValidatorBuilder())->fromYaml($yaml)->getServerRequestValidator();
            $validator->validate($this->makeRequest('deepObject', 'deep'));
        } catch (InvalidQueryArgs $exception) {
            /** @var InvalidParameter $previous */
            $previous = $exception->getPrevious();
            /** @var TypeMismatch $previous */
            $previous = $previous->getPrevious();
            self::assertInstanceOf(TypeMismatch::class, $previous);
            self::assertEquals(['id', 'before', 'first', 'second'], $previous->dataBreadCrumb()->buildChain());
        }
    }

    protected function makeArrayYaml(string $style, string $type, string $format): string
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

    protected function makeRequest(string $style, string $type): ServerRequest
    {
        $map     = [
            'form' => [
                'integer' => '1,2,3',
                'string' => 'id1,id2,id3',
                'boolean' => 'true,false',
                'number' => '1.00,2.00,3.00',
            ],
            'spaceDelimited' => [
                'integer' => '1 2 3',
                'string' => 'id1 id2 id3',
                'boolean' => 'true false',
                'number' => '1.00 2.00 3.00',
            ],
            'pipeDelimited' => [
                'integer' => '1|2|3',
                'string' => 'id1|id2|id3',
                'boolean' => 'true|false',
                'number' => '1.00|2.00|3.00',
            ],
            'deepObject' => [ // we pass all arguments as strings because we can't encode it otherwise using query
                'integer' => ['before' => '10', 'after' => '1'],
                'deep' => ['before' => ['first' => ['second' => '10']], 'after' => 1],
                'error' => ['before' => 'ten', 'after' => 'one'],
                'deepArrayInteger' => ['10', '20'],
                'deepArrayBoolean' => ['true', 'false'],
                'deepArrayStrings' => ['abc', 'def'],
                'deepArrayOfArrayInteger' => [['1', '2'], ['3', '4']],
                'numericKeys' => ['1' => '10', '2' => '20'],
            ],
        ];
        $request = new ServerRequest('GET', 'http://localhost:8000/api/v1/users');
        $request = $request->withQueryParams(['id' => $map[$style][$type]]);

        return $request;
    }
}
