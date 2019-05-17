<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class OneOfTest extends SchemaValidatorTest
{
    public function test_it_validates_oneOf_green() : void
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

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_oneOf_red() : void
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
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('oneOf', $e->keyword());
        }
    }

    public function test_it_validates_oneOf_no_matches_red() : void
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
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('oneOf', $e->keyword());
        }
    }
}
