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

class NotTest extends SchemaValidatorTest
{

    function test_it_validates_not_green()
    {

        $spec = <<<SPEC
schema:
  not:
    type: object
    properties:
      name:
        type: string
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = (object)['name' => 10];

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    function test_it_validates_not_red()
    {

        $spec = <<<SPEC
schema:
  not:
    type: object
    properties:
      name:
        type: string
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = (object)['name' => 'Dima', 'age' => 10];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('not', $e->keyword());
        }
    }

}
