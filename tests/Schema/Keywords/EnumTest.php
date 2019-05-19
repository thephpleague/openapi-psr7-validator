<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\KeywordMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class EnumTest extends SchemaValidatorTest
{
    public function testItValidatesEnumGreen() : void
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

    public function testItValidatesEnumRed() : void
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
        } catch (KeywordMismatch $e) {
            $this->assertEquals('enum', $e->keyword());
        }
    }
}
