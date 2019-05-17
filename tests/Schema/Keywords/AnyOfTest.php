<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class AnyOfTest extends SchemaValidatorTest
{
    public function testItValidatesAnyOfGreen() : void
    {
        $spec = <<<SPEC
schema:
  anyOf:
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
      required:
      - age
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['age' => 10];

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function testItValidatesAnyOfRed() : void
    {
        $spec = <<<SPEC
schema:
  anyOf:
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
      required:
      - age
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['time' => 'today'];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('anyOf', $e->keyword());
        }
    }
}
