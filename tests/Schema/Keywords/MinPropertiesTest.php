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

class MinPropertiesTest extends SchemaValidatorTest
{

    function test_it_validates_minProperties_green()
    {
        $spec = <<<SPEC
schema:
  type: object
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['a' => 1, 'b' => 2];

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    function test_it_validates_minProperties_red()
    {
        $spec = <<<SPEC
schema:
  type: array
  minProperties: 2
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['a' => 1];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('minProperties', $e->keyword());
        }
    }
}
