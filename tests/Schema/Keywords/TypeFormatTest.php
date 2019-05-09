<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);

namespace OpenAPIValidationTests\Schema\Keywords;

use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\TypeFormats\Format;
use OpenAPIValidation\Schema\TypeFormats\FormatsContainer;
use OpenAPIValidation\Schema\Validator;
use OpenAPIValidationTests\Schema\SchemaValidatorTest;

class TypeFormatTest extends SchemaValidatorTest
{
    function test_it_validates_type_format_green()
    {

        $spec = <<<SPEC
schema:
  type: string
  format: email
SPEC;

        $schema = $this->loadRawSchema($spec);
        (new Validator($schema, "valid@email.org"))->validate();
        $this->addToAssertionCount(1);
    }

    function test_it_validates_type_invalid_format_red()
    {

        $spec = <<<SPEC
schema:
  type: string
  format: email
SPEC;

        $schema = $this->loadRawSchema($spec);

        try {
            (new Validator($schema, "invalid email"))->validate();
            $this->fail('Exception expected');
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('email', $e->getPrevious()->format());
        }
    }

    function test_it_unexpected_format_ignored_green()
    {

        $spec = <<<SPEC
schema:
  type: string
  format: unexpected
SPEC;

        $schema = $this->loadRawSchema($spec);
        (new Validator($schema, "valid@email.org"))->validate();
        $this->addToAssertionCount(1);
    }

    function test_it_allows_custom_format_green()
    {

        $spec = <<<SPEC
schema:
  type: string
  format: unexpected
SPEC;

        $unexpectedFormat = new class()
        {
            function __invoke($value): bool
            {
                return $value === "good value";
            }
        };
        FormatsContainer::registerFormat('string', 'unexpected', $unexpectedFormat);

        $schema = $this->loadRawSchema($spec);
        (new Validator($schema, "good value"))->validate();
        $this->addToAssertionCount(1);
    }

    function test_it_allows_custom_format_red()
    {

        $spec = <<<SPEC
schema:
  type: string
  format: unexpected
SPEC;

        $customFormat = function ($value): bool {
            return $value === "good value";
        };
        FormatsContainer::registerFormat('string', 'unexpected', $customFormat);

        try {
            $schema = $this->loadRawSchema($spec);
            (new Validator($schema, "bad value"))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals('unexpected', $e->getPrevious()->format());
        }

    }
}
