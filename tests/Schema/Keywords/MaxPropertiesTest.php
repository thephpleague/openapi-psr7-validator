<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\SchemaValidator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class MaxPropertiesTest extends SchemaValidatorTest
{
    public function testItValidatesMaxPropertiesGreen() : void
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

    public function testItValidatesMaxPropertiesRed() : void
    {
        $spec = <<<SPEC
schema:
  type: array
  maxProperties: 2
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['a' => 1, 'b' => 2, 'c' => 3];

        try {
            (new SchemaValidator())->validate($data, $schema);
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('maxProperties', $e->keyword());
        }
    }
}
