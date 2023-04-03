<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class MultipleOfTest extends SchemaValidatorTest
{
    /**
     * @return number[][] of arguments
     */
    public function validDatasets(): array
    {
        return [
            [4, 2],
            [10.0, 2],
            [10, .5],
            [9.9, .3],
            [.94, .01],
        ];
    }

    /**
     * @return number[][] of arguments
     */
    public function invalidDatasets(): array
    {
        return [
            [4, 3],
            [10.0, 3],
            [10, .11],
            [9.9, .451],
            [.94, .03],
            [1, .3333333],
        ];
    }

    /**
     * @param int|float $number
     * @param int|float $multipleOf
     *
     * @dataProvider validDatasets
     */
    public function testItValidatesMultipleofGreen($number, $multipleOf): void
    {
        $spec = <<<SPEC
schema:
  type: number
  multipleOf: $multipleOf
SPEC;

        $schema = $this->loadRawSchema($spec);

        (new SchemaValidator())->validate($number, $schema);
        $this->addToAssertionCount(1);
    }

    /**
     * @param int|float $number
     * @param int|float $multipleOf
     *
     * @dataProvider invalidDatasets
     */
    public function testItValidatesMultipleofRed($number, $multipleOf): void
    {
        $spec = <<<SPEC
schema:
  type: number
  multipleOf: $multipleOf
SPEC;

        $schema = $this->loadRawSchema($spec);

        try {
            (new SchemaValidator())->validate($number, $schema);
            $this->fail('Validation should not pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('multipleOf', $e->keyword());
        }
    }
}
