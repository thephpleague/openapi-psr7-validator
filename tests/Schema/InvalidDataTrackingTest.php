<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 04 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidationTests\Schema;


use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator;

class InvalidDataTrackingTest extends SchemaValidatorTest
{
    function test_it_shows_invalid_data_address()
    {
        $spec = <<<SPEC
schema:
  type: array
  items:
    type: string
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ["valid1", "valid2", .0];

        try {
            (new Validator($schema, $data))->validate();
        } catch (ValidationKeywordFailed $e) {
            $this->assertEquals([2], $e->dataBreadCrumb()->buildChain());
            $this->assertEquals($data[2], $e->data());
            $this->assertEquals("type", $e->keyword());
        }

    }
}