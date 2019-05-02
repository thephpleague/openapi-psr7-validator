<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

class PropertiesTest extends SchemaValidatorTest
{

    function test_it_validates_properties_green()
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

    function test_it_validates_additional_properties_green()
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

    function test_it_validates_properties_red()
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
            $this->assertEquals('properties', $e->keyword());
        }
    }

    function test_it_validates_additional_properties_red()
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
            $this->assertEquals('properties', $e->keyword());
        }
    }
}
