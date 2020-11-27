<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\TypeMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class AllOfTest extends SchemaValidatorTest
{
    public function testItValidatesAllOfGreen(): void
    {
        $spec = <<<SPEC
schema:
  allOf:
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

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesAllOfRed(): void
    {
        $spec = <<<SPEC
schema:
  allOf:
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
        $data   = ['name' => 'Dima', 'age' => 10.5];

        $this->expectException(TypeMismatch::class);

        (new SchemaValidator())->validate($data, $schema);
    }
}
