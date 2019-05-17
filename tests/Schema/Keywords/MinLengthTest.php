<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class MinLengthTest extends SchemaValidatorTest
{
    public function test_it_validates_minLength_green() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  minLength: 10
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abcde12345';

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_minLength_red() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  minLength: 11
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abcde12345';

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('minLength', $e->keyword());
        }
    }
}
