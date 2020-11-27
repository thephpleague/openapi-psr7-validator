<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\Exception\TypeMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class PropertiesTest extends SchemaValidatorTest
{
    public function testItValidatesPropertiesGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: object
  properties:
    name:
      type: string
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['name' => 'Dima'];

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesAdditionalPropertiesGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: object
  properties:
    name:
      type: string
  additionalProperties:
    type: number
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['age' => 42.5];

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesPropertiesRed(): void
    {
        $spec = <<<SPEC
schema:
  type: object
  properties:
    name:
      type: string
    age:
      type: integer
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['name' => 'Dima', 'age' => 'young'];

        $this->expectException(TypeMismatch::class);
        (new SchemaValidator())->validate($data, $schema);
    }

    public function testItValidatesAdditionalPropertiesRed(): void
    {
        $spec = <<<SPEC
schema:
  type: object
  properties:
    name:
      type: string
  additionalProperties:
    type: number
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['name' => 'Dima', 'age' => 'young'];

        $this->expectException(TypeMismatch::class);
        (new SchemaValidator())->validate($data, $schema);
    }

    public function testItValidatesAdditionalPropertiesDisallowedRed(): void
    {
        $spec = <<<SPEC
schema:
  type: object
  required:
    - data
  properties:
    data: {}
  additionalProperties: false
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['data' => [], 'excessProperty' => 'test'];

        $this->expectException(KeywordMismatch::class);
        (new SchemaValidator())->validate($data, $schema);
    }

    public function testItInfersObjectTypeGreen(): void
    {
        $spec = <<<SPEC
schema:
  properties:
    date:
      type: string
      format: date
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['date' => 'not-a-date'];

        $this->expectException(KeywordMismatch::class);
        (new SchemaValidator())->validate($data, $schema);
    }
}
