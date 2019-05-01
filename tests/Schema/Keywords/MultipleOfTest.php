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

class MultipleOfTest extends SchemaValidatorTest
{
    function test_it_validates_multipleof_green()
    {
        $spec = <<<SPEC
schema:
  type: number
  multipleOf: 2
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 10.0;

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    function test_it_validates_multipleof_red()
    {
        $spec = <<<SPEC
schema:
  type: number
  multipleOf: 2
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 1;

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('multipleOf', $e->keyword());
        }
    }

}
