<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7\Validators;

use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Schema;
use League\OpenAPIValidation\PSR7\Validators\SerializedParameter;
use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use PHPUnit\Framework\TestCase;

class SerializedParameterTest extends TestCase
{
    /**
     * @param mixed[] $parameterData
     *
     * @testWith [{}]
     *           [{"content": {"application/json": {}, "application/xml": {}}}]
     *           [{"content": {"application/json": {}}, "schema": {}}]
     */
    public function testFromSpecThrowsInvalidSchemaExceptionIfParameterIsNotValid(array $parameterData): void
    {
        $this->expectException(InvalidSchema::class);
        SerializedParameter::fromSpec(new Parameter($parameterData));
    }

    public function testDeserializeThrowsSchemaMismatchExceptionIfValueIsNotStringWhenShouldBeDeserialized(): void
    {
        $subject = new SerializedParameter($this->createMock(Schema::class), 'application/json');

        $this->expectException(SchemaMismatch::class);
        $this->expectExceptionMessage("Value expected to be 'string', but 'array' given");

        $subject->deserialize(['green', 'red']);
    }
}
