<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\FormatMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Schema\TypeFormats\FormatsContainer;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class TypeFormatTest extends SchemaValidatorTest
{
    public function testItValidatesTypeFormatGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  format: email
SPEC;

        $schema = $this->loadRawSchema($spec);
        (new SchemaValidator())->validate('valid@email.org', $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesTypeInvalidFormatRed(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  format: email
SPEC;

        $schema = $this->loadRawSchema($spec);

        try {
            (new SchemaValidator())->validate('invalid email', $schema);
            $this->fail('Validation did not expected to pass');
        } catch (FormatMismatch $e) {
            $this->assertEquals('email', $e->format());
        }
    }

    public function testItUnexpectedFormatIgnoredGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  format: unexpected
SPEC;

        $schema = $this->loadRawSchema($spec);
        (new SchemaValidator())->validate('valid@email.org', $schema);
        $this->addToAssertionCount(1);
    }

    public function testItAllowsCustomFormatGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  format: unexpected
SPEC;

        $unexpectedFormat = new class ()
        {
            /**
             * @param mixed $value
             */
            public function __invoke($value): bool
            {
                return $value === 'good value';
            }
        };
        FormatsContainer::registerFormat('string', 'unexpected', $unexpectedFormat);

        $schema = $this->loadRawSchema($spec);
        (new SchemaValidator())->validate('good value', $schema);
        $this->addToAssertionCount(1);
    }

    public function testItAllowsCustomFormatRed(): void
    {
        $spec = <<<SPEC
schema:
  type: string
  format: unexpected
SPEC;

        $customFormat = static function ($value): bool {
            return $value === 'good value';
        };
        FormatsContainer::registerFormat('string', 'unexpected', $customFormat);

        try {
            $schema = $this->loadRawSchema($spec);
            (new SchemaValidator())->validate('bad value', $schema);
            $this->fail('Validation did not expected to pass');
        } catch (FormatMismatch $e) {
            $this->assertEquals('unexpected', $e->format());
        }
    }
}
