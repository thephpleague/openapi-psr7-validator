<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class RequiredTest extends SchemaValidatorTest
{
    public function testItValidatesRequiredGreen() : void
    {
        $spec = <<<SPEC
schema:
  type: object
  required:
  - a
  - b
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['a' => 1, 'b' => 2];

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function testItValidatesPropertiesWriteOnlyGreen() : void
    {
        $spec = <<<SPEC
schema:
  type: object
  properties:
    name:
      type: string
      writeOnly: true
    age:
      type: integer
  required:
  - name
  - age
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['age' => 20];

        (new Validator($schema, $data, Validator::VALIDATE_AS_RESPONSE))->validate();
        $this->addToAssertionCount(1);
    }

    public function testItValidatesRequiredRed() : void
    {
        $spec = <<<SPEC
schema:
  type: array
  required: 
  - a
  - b
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['a' => 1];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('required', $e->keyword());
        }
    }

    public function testItValidatesPropertiesRed() : void
    {
        $spec = <<<SPEC
schema:
  type: object
  properties:
    name:
      type: string
    age:
      type: integer
  required:
  - name
  - age
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['name' => 'Dima'];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('required', $e->keyword());
        }
    }
}
