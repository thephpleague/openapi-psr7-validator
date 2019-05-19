<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\KeywordMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class MinPropertiesTest extends SchemaValidatorTest
{
    public function testItValidatesMinPropertiesGreen() : void
    {
        $spec = <<<SPEC
schema:
  type: object
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['a' => 1, 'b' => 2];

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesMinPropertiesRed() : void
    {
        $spec = <<<SPEC
schema:
  type: array
  minProperties: 2
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['a' => 1];

        try {
            (new SchemaValidator())->validate($data, $schema);
        } catch (KeywordMismatch $e) {
            $this->assertEquals('minProperties', $e->keyword());
        }
    }
}
