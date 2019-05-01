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

class MaximumTest extends SchemaValidatorTest
{
    function test_maximum_nonexclusive_keyword_green()
    {
        $spec = <<<SPEC
schema:
  type: number
  maximum: 100
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 100;

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    function test_maximum_exclusive_keyword_green()
    {
        $spec = <<<SPEC
schema:
  type: number
  maximum: 100
  exclusiveMaximum: true
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 100;

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('maximum', $e->keyword());
        }
    }
}
