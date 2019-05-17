<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class PropertiesTest extends SchemaValidatorTest
{
    public function test_it_validates_properties_green() : void
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

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_additional_properties_green() : void
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

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_properties_red() : void
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

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('type', $e->keyword());
        }
    }

    public function test_it_validates_additional_properties_red() : void
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

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('type', $e->keyword());
        }
    }
}
