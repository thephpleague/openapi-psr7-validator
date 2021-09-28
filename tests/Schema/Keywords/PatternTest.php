<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class PatternTest extends SchemaValidatorTest
{
    /**
     * @return string[][]
     */
    public function validDataProvider(): array
    {
        return [
            ['^[a|b]+$', 'abba'],
            ['foo', 'foo'], // Tests adding anchors
            ['foof', 'foof'], // Tests adding anchors when first and last character is same
            ['1foo1', '1foo1'], // Tests adding anchors when first and last character is same with numbers
            ['^#\d+$', '#123'], // Tests adding anchors to string which has #
            ['^#(\d+)#$', '#123#'], // Tests adding anchors to string which has multiple#
            ['^[А-Я]{2}$', 'ДГ'], // Tests patterns with Unicode
        ];
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testItValidatesPatternGreen(string $pattern, string $data): void
    {
        $spec = <<<SPEC
schema:
  type: string
  pattern: $pattern
SPEC;

        $schema = $this->loadRawSchema($spec);

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesPatternRed(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  pattern: "^[a|b]+$"
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = 'abc';

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (KeywordMismatch $e) {
            $this->assertEquals('pattern', $e->keyword());
        }
    }
}
