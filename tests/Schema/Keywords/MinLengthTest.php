<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class MinLengthTest extends SchemaValidatorTest
{
    public function testItValidatesMinLengthGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  minLength: 10
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abcde12345';

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesMinLengthRed(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  minLength: 11
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abcde12345';

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('minLength', $e->keyword());
        }
    }
}
