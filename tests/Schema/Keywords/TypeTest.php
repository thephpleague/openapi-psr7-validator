<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class TypeTest extends SchemaValidatorTest
{
    public function testItValidatesTypeGreen() : void
    {
        $typedValues = [
            'string' => 'string value',
            'object' => ['a' => 1],
            'array' => ['a', 'b'],
            'boolean' => true,
            'number' => 0.54,
            'integer' => 1,
        ];

        foreach ($typedValues as $type => $validValue) {
            if ($type === 'array') {
                $spec = <<<SPEC
schema:
  type: array
  items:
    type: string
SPEC;
            } else {
                $spec = <<<SPEC
schema:
  type: $type
SPEC;
            }

            $schema = $this->loadRawSchema($spec);

            (new Validator($schema, $validValue))->validate();
            $this->addToAssertionCount(1);
        }
    }

    public function testItValidatesTypeRed() : void
    {
        $typedValues = [
            'string' => 12,
            'object' => 'not object',
            'array' => ['a' => 1, 'b' => 2], // this is not a plain array (ala JSON)
            'boolean' => [1, 2],
            'number' => [],
            'integer' => 12.55,
        ];

        foreach ($typedValues as $type => $invalidValue) {
            $spec = <<<SPEC
schema:
  type: $type
SPEC;

            $schema = $this->loadRawSchema($spec);

            try {
                (new Validator($schema, $invalidValue))->validate();
            } catch (ValidationKeywordFailed $e) {
                $this->assertEquals('type', $e->keyword());
            }
        }
    }
}
