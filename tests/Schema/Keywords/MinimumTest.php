<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class MinimumTest extends SchemaValidatorTest
{
    /**
     * @testWith [100]
     *           [0]
     *           [-1]
     */
    public function testMinimumNonexclusiveKeywordGreen(int $data): void
    {
        $spec = <<<SPEC
schema:
  type: number
  minimum: {$data}
SPEC;

        $schema = $this->loadRawSchema($spec);

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testMinimumExclusiveKeywordGreen(): void
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
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('minimum', $e->keyword());
            $this->assertEquals('Keyword validation failed: Value 100 must be greater than 100', $e->getMessage());
        }
    }
}
