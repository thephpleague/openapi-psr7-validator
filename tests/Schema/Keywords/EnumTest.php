<?php

declare(strict_types=1);

namespace OpenClassrooms\OpenAPIValidation\Tests\Schema\Keywords;

use OpenClassrooms\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use OpenClassrooms\OpenAPIValidation\Schema\SchemaValidator;
use OpenClassrooms\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class EnumTest extends SchemaValidatorTest
{
    public function testItValidatesEnumGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  enum:
  - a
  - b
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'a';

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesEnumRed(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  enum: 
  - a
  - b
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'c';

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('enum', $e->keyword());
        }
    }

    public function testItDisplaysAllowedValuesInErrorMessage(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  enum:
  - apple
  - banana
  - cherry
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'orange';

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('enum', $e->keyword());
            $this->assertEquals('Keyword validation failed: Value must be present in the enum. Allowed values: \'apple\', \'banana\', \'cherry\'', $e->getMessage());
        }
    }

    public function testItDisplaysNumericEnumValuesWithoutQuotes(): void
    {
        $spec = <<<SPEC
schema:
  type: integer
  enum:
  - 1
  - 2
  - 3
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 5;

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('enum', $e->keyword());
            $this->assertEquals('Keyword validation failed: Value must be present in the enum. Allowed values: 1, 2, 3', $e->getMessage());
        }
    }
}
