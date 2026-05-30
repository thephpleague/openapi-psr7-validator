<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class ConstValueTest extends SchemaValidatorTest
{
    public function testItValidatesConstStringGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  const: "doc"
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'doc';

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesConstStringRed(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  const: "doc"
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'invalid';

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('const', $e->keyword());
        }
    }

    public function testItValidatesConstIntegerGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: integer
  const: 42
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 42;

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesConstIntegerRed(): void
    {
        $spec = <<<SPEC
schema:
  type: integer
  const: 42
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 43;

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('const', $e->keyword());
        }
    }

    public function testItValidatesConstBooleanGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: boolean
  const: true
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = true;

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesConstNullGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: "null"
  const: null
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = null;

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }
}
