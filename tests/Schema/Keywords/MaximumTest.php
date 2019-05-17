<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class MaximumTest extends SchemaValidatorTest
{
    public function testMaximumNonexclusiveKeywordGreen() : void
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

    public function testMaximumExclusiveKeywordGreen() : void
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
