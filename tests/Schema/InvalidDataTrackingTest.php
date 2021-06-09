<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema;

use League\OpenAPIValidation\Schema\Exception\TypeMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;

final class InvalidDataTrackingTest extends SchemaValidatorTest
{
    public function testItShowsInvalidDataAddress(): void
    {
        $spec = <<<SPEC
schema:
  type: array
  items:
    type: string
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['valid1', 'valid2', .0];

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (TypeMismatch $e) {
            $this->assertEquals([2], $e->dataBreadCrumb()->buildChain());
            $this->assertEquals($data[2], $e->data());
        }
    }

    public function testItShowsInvalidDataAddressNested(): void
    {
        $spec = <<<SPEC
schema:
  type: array
  items:
    type: array
    items:
      type: object
      properties:
        name: 
          type: string     
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [
            [
                ['name' => 'good name'],
            ],
            [
                ['name' => .0],
            ],
        ];

        try {
            (new SchemaValidator())->validate($data, $schema);
            $this->fail('Validation did not expected to pass');
        } catch (TypeMismatch $e) {
            $this->assertEquals([1, 0, 'name'], $e->dataBreadCrumb()->buildChain());
            $this->assertEquals($data[1][0]['name'], $e->data());
        }
    }
}
