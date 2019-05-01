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

class NullableTest extends SchemaValidatorTest
{

    function test_it_validates_nullable_green()
    {

        $spec = <<<SPEC
schema:
    type: string
    nullable: true
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = null;

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    function test_it_validates_nullable_red()
    {

        $spec = <<<SPEC
schema:
    type: string
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = null;

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('nullable', $e->keyword());
        }
    }
}
