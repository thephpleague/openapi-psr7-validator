<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class OneOfTest extends SchemaValidatorTest
{
    public function testItValidatesOneOfGreen(): void
    {
        $spec = <<<SPEC
schema:
  oneOf:
    - type: object
      properties:
        name:
          type: string
      required:
      - name
    - type: object
      properties:
        age:
          type: integer
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['age' => 10];

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesOneOfRed(): void
    {
        $spec = <<<SPEC
schema:
  oneOf:
    - type: object
      properties:
        name:
          type: string
    - type: object
      properties:
        age:
          type: integer
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['name' => 'Dima', 'age' => 10];

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('oneOf', $e->keyword());
        }
    }

    public function testItValidatesOneOfNoMatchesRed(): void
    {
        $spec = <<<SPEC
schema:
  oneOf:
    - type: object
      properties:
        name:
          type: string
    - type: object
      properties:
        age:
          type: integer
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['name' => 500, 'age' => 'young'];

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('oneOf', $e->keyword());
        }
    }

    public function testItValidatesOneOfGreenWithDiscriminator(): void
    {
        $spec = <<<SPEC
schema:
  discriminator: 
    propertyName: type
    mapping:
      NAME: 0
      TIME: 1
  oneOf:
    - type: object
      properties:
        type: 
          type: string
        name:
          type: string
      required:
      - type
      - name
    - type: object
      properties:
        type: 
          type: string
        age:
          type: integer
      required:
      - type
      - age
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [
            'type' => 'NAME',
            'name' => 'John',
        ];

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesOneOfRedWithDiscriminator(): void
    {
        $spec = <<<SPEC
schema:
  discriminator: 
    propertyName: type
    mapping:
      NAME: 0
      TIME: 1
  oneOf:
    - type: object
      properties:
        type: 
          type: string
        name:
          type: string
      required:
      - type
      - name
    - type: object
      properties:
        type: 
          type: string
        age:
          type: integer
      required:
      - type
      - age
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [
            'type' => 'TIME',
            'age' => 'today',
        ];

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('oneOf', $e->keyword());
        }
    }
}
