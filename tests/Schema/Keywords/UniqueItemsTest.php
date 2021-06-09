<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class UniqueItemsTest extends SchemaValidatorTest
{
    /**
     * @return array<array<(string|array<mixed>)>>
     */
    public function dataProviderGreen(): array
    {
        return [
            [
                <<<SPEC
schema:
  type: array
  items:
    type: integer
SPEC
,
                [],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: integer
SPEC
,
                [1, 1],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: integer
  uniqueItems: true
SPEC
,
                [1, 2],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: number
  uniqueItems: true
SPEC
,
                [1, 1.0],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: boolean
  uniqueItems: true
SPEC
,
                [true, false],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: string
  uniqueItems: true
SPEC
,
                ['one', 'oNe'],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: object
  uniqueItems: true
SPEC
,
                [['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: array
    items:
        type: object
  uniqueItems: true
SPEC
,
                [[['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]], [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2]]],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: array
    items:
        type: object
    uniqueItems: true
  uniqueItems: true
SPEC
,
                [[['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]], [['a' => 1, 'b' => 2], ['a' => 3, 'b' => 5]]],
            ],
        ];
    }

    /**
     * @return array<array<(string|array<mixed>)>>
     */
    public function dataProviderRed(): array
    {
        return [
            [
                <<<SPEC
schema:
  type: array
  items:
    type: integer
  uniqueItems: true
SPEC
,
                [1, 1],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: boolean
  uniqueItems: true
SPEC
,
                [true, true],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: string
  uniqueItems: true
SPEC
,
                ['one', 'one'],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: object
  uniqueItems: true
SPEC
,
                [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2]],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: array
    items:
        type: object
  uniqueItems: true
SPEC
,
                [[['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]], [['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]]],
            ],
            [
                <<<SPEC
schema:
  type: array
  items:
    type: array
    items:
        type: object
    uniqueItems: true
  uniqueItems: true
SPEC
,
                [[['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]], [['a' => 1, 'b' => 2], ['a' => 1, 'b' => 2]]],
            ],
        ];
    }

    /**
     * @param array<mixed> $data
     *
     * @dataProvider dataProviderGreen
     */
    public function testsGreen(string $spec, array $data): void
    {
        $schema = $this->loadRawSchema($spec);

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    /**
     * @param array<mixed> $data
     *
     * @dataProvider dataProviderRed
     */
    public function testsRed(string $spec, array $data): void
    {
        $schema = $this->loadRawSchema($spec);

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Exception expected');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('uniqueItems', $e->keyword());
        }
    }
}
