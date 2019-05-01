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
use OpenAPIValidation\Schema\Keywords\OneOf;
use OpenAPIValidation\Schema\Keywords\Pattern;
use OpenAPIValidation\Schema\Keywords\Properties;
use OpenAPIValidation\Schema\Keywords\Required;
use OpenAPIValidation\Schema\Keywords\Type;
use OpenAPIValidation\Schema\Keywords\UniqueItems;

// This will load a whole schema and data to validate if one matches another
class Validator
{
    /** @var CebeSchema */
    protected $schema;
    /** @var mixed */
    protected $data;

    /**
     * @param CebeSchema $schema
     * @param mixed $data
     */
    public function __construct(CebeSchema $schema, $data)
    {
        $this->schema = $schema;
        $this->data   = $data;
    }

    /**
     * Apply a whole bunch of possible checks by using validation keywords
     */
    public function validate(): void
    {
        //
        // This keywords come directly from JSON Schema Validation, they are the same as in JSON schema
        // https://tools.ietf.org/html/draft-wright-json-schema-validation-00#section-5
        //
        if (isset($this->schema->multipleOf)) {
            (new MultipleOf())->validate($this->data, $this->schema->multipleOf);
        }

        if (isset($this->schema->maximum)) {
            $exclusiveMaximum = (bool)(isset($this->schema->exclusiveMaximum) ? $this->schema->exclusiveMaximum : false);
            (new Maximum())->validate($this->data, $this->schema->maximum, $exclusiveMaximum);
        }

        if (isset($this->schema->minimum)) {
            $exclusiveMinimum = (bool)(isset($this->schema->exclusiveMinimum) ? $this->schema->exclusiveMinimum : false);
            (new Minimum())->validate($this->data, $this->schema->minimum, $exclusiveMinimum);
        }

        if (isset($this->schema->maxLength)) {
            (new MaxLength())->validate($this->data, $this->schema->maxLength);
        }

        if (isset($this->schema->minLength)) {
            (new MinLength())->validate($this->data, $this->schema->minLength);
        }

        if (isset($this->schema->pattern)) {
            (new Pattern())->validate($this->data, $this->schema->pattern);
        }

        if (isset($this->schema->maxItems)) {
            (new MaxItems())->validate($this->data, $this->schema->maxItems);
        }

        if (isset($this->schema->minItems)) {
            (new MinItems())->validate($this->data, $this->schema->minItems);
        }

        if (isset($this->schema->uniqueItems)) {
            (new UniqueItems())->validate($this->data, $this->schema->uniqueItems);
        }

        if (isset($this->schema->maxProperties)) {
            (new MaxProperties())->validate($this->data, $this->schema->maxProperties);
        }

        if (isset($this->schema->minProperties)) {
            (new MinProperties())->validate($this->data, $this->schema->minProperties);
        }

        if (isset($this->schema->required)) {
            (new Required())->validate($this->data, $this->schema->required);
        }

        if (isset($this->schema->enum)) {
            (new Enum())->validate($this->data, $this->schema->enum);
        }

        //
        // The following properties are taken from the JSON Schema definition but their definitions were adjusted to the OpenAPI Specification.
        //

        if (isset($this->schema->type)) {
            (new Type($this->schema))->validate($this->data, $this->schema->type);
        }

        if (isset($this->schema->items)) {
            (new Items($this->schema))->validate($this->data, $this->schema->items);
        }

        if (isset($this->schema->properties) && count($this->schema->properties)) {
            $additionalProperties = isset($this->schema->additionalProperties) ? $this->schema->additionalProperties : null;
            (new Properties($this->schema))->validate($this->data, $this->schema->properties, $additionalProperties);
        }

        if (isset($this->schema->allOf) && count($this->schema->allOf)) {
            (new AllOf())->validate($this->data, $this->schema->allOf);
        }

        if (isset($this->schema->oneOf) && count($this->schema->oneOf)) {
            (new OneOf())->validate($this->data, $this->schema->oneOf);
        }

        if (isset($this->schema->anyOf) && count($this->schema->anyOf)) {
            (new AnyOf())->validate($this->data, $this->schema->anyOf);
        }

        if (isset($this->schema->not)) {
            (new Not())->validate($this->data, $this->schema->not);
        }

        // ok, all checks are done
    }
}