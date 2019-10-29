<?php

declare(strict_types=1);

namespace League\OpenAPIValidationTests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class PatternTest extends SchemaValidatorTest
{
    public function testItValidatesPatternGreen() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  pattern: "#^[a|b]+$#"
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abba';

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesPatternRed() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  pattern: "#^[a|b]+$#"
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abc';

        try {
            (new SchemaValidator())->validate($data, $schema);
        } catch (KeywordMismatch $e) {
            $this->assertEquals('pattern', $e->keyword());
        }
    }
}
