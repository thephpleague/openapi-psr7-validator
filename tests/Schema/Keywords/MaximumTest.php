<?php

declare(strict_types=1);

namespace League\OpenAPIValidationTests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidationTests\Schema\SchemaValidatorTest;

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

        (new SchemaValidator())->validate($data, $schema);
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
            (new SchemaValidator())->validate($data, $schema);
        } catch (KeywordMismatch $e) {
            $this->assertEquals('maximum', $e->keyword());
        }
    }
}
