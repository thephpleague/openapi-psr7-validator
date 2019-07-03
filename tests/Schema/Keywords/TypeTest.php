<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\TypeMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class TypeTest extends SchemaValidatorTest
{
    /**
     * @return array<array<string, mixed>>
     */
    public function validDataProvider() : array
    {
        return [
            ['string', 'string value'],
            ['object', ['a' => 1]],
            ['array', ['a', 'b']],
            ['boolean', true],
            ['boolean', false],
            ['boolean', 'True'],
            ['boolean', 'false'],
            ['number', 12],
            ['number', '12'],
            ['number', 0.123],
            ['number', '0.123'],
            ['integer', 12],
            ['integer', '12'],
        ];
    }

    /**
     * @param mixed $validValue
     *
     * @dataProvider validDataProvider
     */
    public function testItValidatesTypeGreen(string $type, $validValue) : void
    {
        $spec = <<<SPEC
schema:
  type: $type
SPEC;

        $schema = $this->loadRawSchema($spec);

        (new SchemaValidator())->validate($validValue, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesTypeRed() : void
    {
        $typedValues = [
            'string'  => 12,
            'object'  => 'not object',
            'array'   => ['a' => 1, 'b' => 2], // this is not a plain array (ala JSON)
            'boolean' => [1, 2],
            'number'  => [],
            'integer' => 12.55,
        ];

        foreach ($typedValues as $type => $invalidValue) {
            $spec = <<<SPEC
schema:
  type: $type
SPEC;

            $schema = $this->loadRawSchema($spec);

            $this->expectException(TypeMismatch::class);
            (new SchemaValidator())->validate($invalidValue, $schema);
        }
    }
}
