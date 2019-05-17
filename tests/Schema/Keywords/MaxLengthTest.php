<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class MaxLengthTest extends SchemaValidatorTest
{
    public function test_it_validates_maxLength_green() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  maxLength: 10
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abcde12345';

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_maxLength_red() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  maxLength: 9
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abcde12345';

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('maxLength', $e->keyword());
        }
    }
}
