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

class RequiredTest extends SchemaValidatorTest
{
    function test_it_validates_required_green()
    {
        $spec = <<<SPEC
schema:
  type: object
  required:
  - a
  - b
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = (object)['a' => 1, 'b' => 2];

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    function test_it_validates_properties_writeOnly_green()
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
        $data   = (object)['age' => 20];

        (new Validator($schema, $data, Validator::VALIDATE_AS_RESPONSE))->validate();
        $this->addToAssertionCount(1);
    }


    function test_it_validates_required_red()
    {
        $spec = <<<SPEC
schema:
  type: array
  required: 
  - a
  - b
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = (object)['a' => 1];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('required', $e->keyword());
        }
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
  required:
  - name
  - age
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = (object)['name' => 'Dima'];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('required', $e->keyword());
        }
    }


}
