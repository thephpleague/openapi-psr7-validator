<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class MinItemsTest extends SchemaValidatorTest
{
    public function test_it_validates_minItems_green() : void
    {
        $spec = <<<SPEC
schema:
  type: array
  minItems: 3
  items:
    type: number
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [1, 2, 3];

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_minItems_red() : void
    {
        $spec = <<<SPEC
schema:
  type: array
  minItems: 3
  items:
    type: number
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [1, 2];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('minItems', $e->keyword());
        }
    }
}
