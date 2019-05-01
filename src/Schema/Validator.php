<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema;

use cebe\openapi\spec\Schema as CebeSchema;
use OpenAPIValidation\Schema\Keywords\AllOf;
use OpenAPIValidation\Schema\Keywords\AnyOf;
use OpenAPIValidation\Schema\Keywords\Enum;
use OpenAPIValidation\Schema\Keywords\Items;
use OpenAPIValidation\Schema\Keywords\Maximum;
use OpenAPIValidation\Schema\Keywords\MaxItems;
use OpenAPIValidation\Schema\Keywords\MaxLength;
use OpenAPIValidation\Schema\Keywords\MaxProperties;
use OpenAPIValidation\Schema\Keywords\Minimum;
use OpenAPIValidation\Schema\Keywords\MinItems;
use OpenAPIValidation\Schema\Keywords\MinLength;
use OpenAPIValidation\Schema\Keywords\MinProperties;
use OpenAPIValidation\Schema\Keywords\MultipleOf;
use OpenAPIValidation\Schema\Keywords\Not;
use OpenAPIValidation\Schema\Keywords\Nullable;
use OpenAPIValidation\Schema\Keywords\OneOf;
use OpenAPIValidation\Schema\Keywords\Pattern;
use OpenAPIValidation\Schema\Keywords\Properties;
use OpenAPIValidation\Schema\Keywords\Required;
use OpenAPIValidation\Schema\Keywords\Type;
use OpenAPIValidation\Schema\Keywords\UniqueItems;

// This will load a whole schema and data to validate if one matches another
class Validator
{
    const VALIDATE_AS_REQUEST  = 0;
    const VALIDATE_AS_RESPONSE = 1;

    /** @var CebeSchema */
    protected $schema;
    /** @var mixed */
    protected $data;
    /** @var int type of the data that we validate - Request or Response (affected by writeOnly/readOnly) */
    protected $dataType;

    /**
     * @param CebeSchema $schema
     * @param mixed $data
     * @param int $dataType
     */
    public function __construct(CebeSchema $schema, $data, $dataType = self::VALIDATE_AS_RESPONSE)
    {
        \Respect\Validation\Validator::in([self::VALIDATE_AS_REQUEST, self::VALIDATE_AS_RESPONSE])->assert($dataType);

        $this->schema   = $schema;
        $this->data     = $data;
        $this->dataType = $dataType;
    }

    /**
     * @return CebeSchema
     */
    public function schema(): CebeSchema
    {
        return $this->schema;
    }

    /**
     * @return mixed
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function dataType(): int
    {
        return $this->dataType;
    }


    /**
     * Apply a whole bunch of possible checks by using validation keywords
     */
    public function validate(): void
    {
        //
        // These keywords are not part of the JSON Schema at all (new to OAS)
        //
        (new Nullable($this->schema, $this))->validate($this->data, $this->schema->nullable);


        //
        // This keywords come directly from JSON Schema Validation, they are the same as in JSON schema
        // https://tools.ietf.org/html/draft-wright-json-schema-validation-00#section-5
        //
        if (isset($this->schema->multipleOf)) {
            (new MultipleOf($this->schema, $this))->validate($this->data, $this->schema->multipleOf);
        }

        if (isset($this->schema->maximum)) {
            $exclusiveMaximum = (bool)(isset($this->schema->exclusiveMaximum) ? $this->schema->exclusiveMaximum : false);
            (new Maximum($this->schema, $this))->validate($this->data, $this->schema->maximum, $exclusiveMaximum);
        }

        if (isset($this->schema->minimum)) {
            $exclusiveMinimum = (bool)(isset($this->schema->exclusiveMinimum) ? $this->schema->exclusiveMinimum : false);
            (new Minimum($this->schema, $this))->validate($this->data, $this->schema->minimum, $exclusiveMinimum);
        }

        if (isset($this->schema->maxLength)) {
            (new MaxLength($this->schema, $this))->validate($this->data, $this->schema->maxLength);
        }

        if (isset($this->schema->minLength)) {
            (new MinLength($this->schema, $this))->validate($this->data, $this->schema->minLength);
        }

        if (isset($this->schema->pattern)) {
            (new Pattern($this->schema, $this))->validate($this->data, $this->schema->pattern);
        }

        if (isset($this->schema->maxItems)) {
            (new MaxItems($this->schema, $this))->validate($this->data, $this->schema->maxItems);
        }

        if (isset($this->schema->minItems)) {
            (new MinItems($this->schema, $this))->validate($this->data, $this->schema->minItems);
        }

        if (isset($this->schema->uniqueItems)) {
            (new UniqueItems($this->schema, $this))->validate($this->data, $this->schema->uniqueItems);
        }

        if (isset($this->schema->maxProperties)) {
            (new MaxProperties($this->schema, $this))->validate($this->data, $this->schema->maxProperties);
        }

        if (isset($this->schema->minProperties)) {
            (new MinProperties($this->schema, $this))->validate($this->data, $this->schema->minProperties);
        }

        if (isset($this->schema->required)) {
            (new Required($this->schema, $this))->validate($this->data, $this->schema->required);
        }

        if (isset($this->schema->enum)) {
            (new Enum($this->schema, $this))->validate($this->data, $this->schema->enum);
        }

        //
        // The following properties are taken from the JSON Schema definition but their definitions were adjusted to the OpenAPI Specification.
        //

        if (isset($this->schema->type)) {
            (new Type($this->schema, $this))->validate($this->data, $this->schema->type);
        }

        if (isset($this->schema->items)) {
            (new Items($this->schema, $this))->validate($this->data, $this->schema->items);
        }

        if (isset($this->schema->properties) && count($this->schema->properties)) {
            $additionalProperties = isset($this->schema->additionalProperties) ? $this->schema->additionalProperties : null;
            (new Properties($this->schema, $this))->validate($this->data, $this->schema->properties, $additionalProperties);
        }

        if (isset($this->schema->allOf) && count($this->schema->allOf)) {
            (new AllOf($this->schema, $this))->validate($this->data, $this->schema->allOf);
        }

        if (isset($this->schema->oneOf) && count($this->schema->oneOf)) {
            (new OneOf($this->schema, $this))->validate($this->data, $this->schema->oneOf);
        }

        if (isset($this->schema->anyOf) && count($this->schema->anyOf)) {
            (new AnyOf($this->schema, $this))->validate($this->data, $this->schema->anyOf);
        }

        if (isset($this->schema->not)) {
            (new Not($this->schema, $this))->validate($this->data, $this->schema->not);
        }


        // ok, all checks are done
    }
}