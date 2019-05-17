<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class NotTest extends SchemaValidatorTest
{
    public function test_it_validates_not_green() : void
    {
        $spec = <<<SPEC
schema:
  not:
    type: object
    properties:
      name:
        type: string
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['name' => 10];

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_not_red() : void
    {
        $spec = <<<SPEC
schema:
  not:
    type: object
    properties:
      name:
        type: string
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['name' => 'Dima', 'age' => 10];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('not', $e->keyword());
        }
    }
}
