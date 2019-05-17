<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class AllOfTest extends SchemaValidatorTest
{
    public function testItValidatesAllOfGreen() : void
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

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function testItValidatesAllOfRed() : void
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

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('type', $e->keyword());
        }
    }
}
