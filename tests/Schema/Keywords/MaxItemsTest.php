<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\KeywordMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class MaxItemsTest extends SchemaValidatorTest
{
    public function testItValidatesMaxItemsGreen() : void
    {
        $spec = <<<SPEC
schema:
  type: array
  maxItems: 3
  items:
    type: number
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [1, 2, 3];

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesMaxItemsRed() : void
    {
        $spec = <<<SPEC
schema:
  type: array
  maxItems: 3
  items:
    type: number
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [1, 2, 3, 4];

        try {
            (new SchemaValidator())->validate($data, $schema);
        } catch (KeywordMismatch $e) {
            $this->assertEquals('maxItems', $e->keyword());
        }
    }
}
