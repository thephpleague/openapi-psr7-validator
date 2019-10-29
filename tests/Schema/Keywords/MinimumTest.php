<?php

declare(strict_types=1);

namespace League\OpenAPIValidationTests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class MinimumTest extends SchemaValidatorTest
{
    public function testMinimumNonexclusiveKeywordGreen() : void
    {
        $spec = <<<SPEC
schema:
  type: number
  minimum: 100
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 100;

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testMinimumExclusiveKeywordGreen() : void
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
            (new SchemaValidator())->validate($data, $schema);
        } catch (KeywordMismatch $e) {
            $this->assertEquals('minimum', $e->keyword());
        }
    }
}
