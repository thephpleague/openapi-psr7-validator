<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

class UniqueItemsTest extends SchemaValidatorTest
{
    function test_it_validates_uniqueItems_green() : void
    {
        $spec = <<<SPEC
schema:
  type: array
  items:
    type: integer
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [1, 1];

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    function test_it_validates_uniqueItems_red() : void
    {
        $spec = <<<SPEC
schema:
  type: array
  items:
    type: integer
  uniqueItems: true
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [1, 1];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('uniqueItems', $e->keyword());
        }
    }
}
