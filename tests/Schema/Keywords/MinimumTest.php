<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class MinimumTest extends SchemaValidatorTest
{
    public function test_minimum_nonexclusive_keyword_green() : void
    {
        $spec = <<<SPEC
schema:
  type: number
  minimum: 100
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 100;

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function test_minimum_exclusive_keyword_green() : void
    {
        $spec = <<<SPEC
schema:
  type: number
  minimum: 100
  exclusiveMinimum: true
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 100;

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('minimum', $e->keyword());
        }
    }
}
