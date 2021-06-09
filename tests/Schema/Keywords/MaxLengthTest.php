<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class MaxLengthTest extends SchemaValidatorTest
{
    public function testItValidatesMaxLengthGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  maxLength: 10
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abcde12345';

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesMaxLengthRed(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  maxLength: 9
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abcde12345';

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('maxLength', $e->keyword());
        }
    }
}
