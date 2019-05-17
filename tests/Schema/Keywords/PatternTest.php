<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class PatternTest extends SchemaValidatorTest
{
    public function test_it_validates_pattern_green() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  pattern: "#^[a|b]+$#"
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abba';

        (new Validator($schema, $data))->validate();
        $this->addToAssertionCount(1);
    }

    public function test_it_validates_pattern_red() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  pattern: "#^[a|b]+$#"
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abc';

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('pattern', $e->keyword());
        }
    }
}
