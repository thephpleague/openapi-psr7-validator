<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\TypeFormats\FormatsContainer;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

final class TypeFormatTest extends SchemaValidatorTest
{
    public function testItValidatesTypeFormatGreen() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  format: email
SPEC;

        $schema = $this->loadRawSchema($spec);
        (new Validator($schema, 'valid@email.org'))->validate();
        $this->addToAssertionCount(1);
    }

    public function testItValidatesTypeInvalidFormatRed() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  format: email
SPEC;

        $schema = $this->loadRawSchema($spec);

        try {
            (new Validator($schema, 'invalid email'))->validate();
            $this->fail('Exception expected');
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('email', $e->getPrevious()->format());
        }
    }

    public function testItUnexpectedFormatIgnoredGreen() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  format: unexpected
SPEC;

        $schema = $this->loadRawSchema($spec);
        (new Validator($schema, 'valid@email.org'))->validate();
        $this->addToAssertionCount(1);
    }

    public function testItAllowsCustomFormatGreen() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  format: unexpected
SPEC;

        $unexpectedFormat = new class()
        {
            /**
             * @param mixed $value
             */
            public function __invoke($value) : bool
            {
                return $value === 'good value';
            }
        };
        FormatsContainer::registerFormat('string', 'unexpected', $unexpectedFormat);

        $schema = $this->loadRawSchema($spec);
        (new Validator($schema, 'good value'))->validate();
        $this->addToAssertionCount(1);
    }

    public function testItAllowsCustomFormatRed() : void
    {
        $spec = <<<SPEC
schema:
  type: string
  format: unexpected
SPEC;

        $customFormat = static function ($value) : bool {
            return $value === 'good value';
        };
        FormatsContainer::registerFormat('string', 'unexpected', $customFormat);

        try {
            $schema = $this->loadRawSchema($spec);
            (new Validator($schema, 'bad value'))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('unexpected', $e->getPrevious()->format());
        }
    }
}
