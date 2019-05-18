<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\SchemaValidator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class MultipleOfTest extends SchemaValidatorTest
{
    public function testItValidatesMultipleofGreen() : void
    {
        $spec = <<<SPEC
schema:
  type: number
  multipleOf: 2
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 10.0;

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesMultipleofRed() : void
    {
        $spec = <<<SPEC
schema:
  type: number
  multipleOf: 2
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 1;

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation should not pass');
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('multipleOf', $e->keyword());
        }
    }
}
