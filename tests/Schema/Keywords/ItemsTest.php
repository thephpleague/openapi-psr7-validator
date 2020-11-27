<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\TypeMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use League\OpenAPIValidation\Tests\Schema\SchemaValidatorTest;

final class ItemsTest extends SchemaValidatorTest
{
    public function testItValidatesItemsGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: array
  items:
    type: string
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = ['stringA', 'stringB'];

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesItemsNestedGreen(): void
    {
        $spec = <<<SPEC
schema:
  type: array
  items:
    type: array
    items:
      type: string
    minItems: 2
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [['stringA', 'stringB'], ['stringC', 'stringD', 'stringE']];

        (new SchemaValidator())->validate($data, $schema);
        $this->addToAssertionCount(1);
    }

    public function testItValidatesItemsRed(): void
    {
        $spec = <<<SPEC
schema:
  type: array
  items:
    type: string
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [1, 2];

        $this->expectException(TypeMismatch::class);
        (new SchemaValidator())->validate($data, $schema);
    }

    public function testItValidatesItemsNestedRed(): void
    {
        $spec = <<<SPEC
schema:
  type: array
  items:
    type: array
    items:
      type: string
    minItems: 2
SPEC;

        $schema = $this->loadRawSchema($spec);
        $data   = [['stringA', 'stringB'], [12, 13]];

        $this->expectException(TypeMismatch::class);

        (new SchemaValidator())->validate($data, $schema);
    }
}
