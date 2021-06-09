<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\TypeMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;
use stdClass;

final class TypeTest extends SchemaValidatorTest
{
    /**
     * @return mixed[][]
     */
    public function validDataProvider(): array
    {
        return [
            ['string', null, 'string value'],
            ['object', null, ['a' => 1]],
            ['array', null, ['a', 'b']],
            ['boolean', null, true],
            ['boolean', null, false],
            ['number', null, 12],
            ['number', null, 0.123],
            ['integer', null, 12],
        ];
    }

    /**
     * @param mixed $validValue
     *
     * @dataProvider validDataProvider
     */
    public function testItValidatesTypeGreen(string $type, ?string $format, $validValue): void
    {
        $spec = <<<SPEC
schema:
  type: $type\n
SPEC;
        if ($format) {
            $spec .= <<<SPEC
  format: $format\n
SPEC;
        }

        $schema = $this->loadRawSchema($spec);

        (new SchemaValidator())->validate($validValue, $schema);
        $this->addToAssertionCount(1);
    }

    /**
     * @param mixed $invalidValue
     *
     * @dataProvider invalidDataProvider
     */
    public function testItValidatesTypeRed(string $type, $invalidValue): void
    {
        $spec = <<<SPEC
schema:
  type: $type\n
SPEC;

        $schema = $this->loadRawSchema($spec);

        $this->expectException(TypeMismatch::class);
        (new SchemaValidator())->validate($invalidValue, $schema);
    }

    /**
     * @return mixed[][]
     */
    public function invalidDataProvider(): array
    {
        return [
            ['string', 12],
            ['object', 'not object'],
            ['array', ['a' => 1, 'b' => 2]], // this is not a plain array (a-la JSON)
            ['boolean', [1, 2]],
            ['boolean', 'True'],
            ['boolean', ''],
            ['boolean', 0],
            ['number', []],
            ['number', '12'],
            ['number', '0.123'],
            ['number', '-0.123'],
            ['integer', 12.55],
            ['integer', '12'],
            ['integer', '-12'],
            ['integer', new stdClass()],
            ['integer', 1.0],
        ];
    }
}
