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

class MaxLengthTest extends SchemaValidatorTest
{

    function test_it_validates_maxLength_green()
    {
        $spec = <<<SPEC
schema:
  type: string
  maxLength: 10
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = "abcde12345";

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    function test_it_validates_maxLength_red()
    {
        $spec = <<<SPEC
schema:
  type: string
  maxLength: 9
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = "abcde12345";

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('maxLength', $e->keyword());
        }
    }
}
