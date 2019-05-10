<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema;

use cebe\openapi\spec\Schema as CebeSchema;
use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
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
use function count;

// This will load a whole schema and data to validate if one matches another
class Validator
{
    // How to treat the data (affects writeOnly/readOnly keywords)
    public const VALIDATE_AS_REQUEST  = 0;
    public const VALIDATE_AS_RESPONSE = 1;

    /** @var CebeSchema */
    protected $schema;
    /** @var mixed */
    protected $data;
    /** @var BreadCrumb */
    protected $dataBreadCrumb;
    /** @var int strategy of validation - Request or Response (affected by writeOnly/readOnly keywords) */
    protected $validationStrategy;

    /**
     * @param mixed $data
     */
    public function __construct(CebeSchema $schema, $data, int $validationStrategy = self::VALIDATE_AS_RESPONSE, ?BreadCrumb $breadCrumb = null)
    {
        \Respect\Validation\Validator::in([self::VALIDATE_AS_REQUEST, self::VALIDATE_AS_RESPONSE])->assert($validationStrategy);

        $this->schema             = $schema;
        $this->data               = $data;
        $this->dataBreadCrumb     = $breadCrumb;
        $this->validationStrategy = $validationStrategy;
    }

    /**
     * Apply a whole bunch of possible checks by using validation keywords
     */
    public function validate() : void
    {
        try {
            // These keywords are not part of the JSON Schema at all (new to OAS)
            (new Nullable($this->schema))->validate($this->data, $this->schema->nullable);

            // This keywords come directly from JSON Schema Validation, they are the same as in JSON schema
            // https://tools.ietf.org/html/draft-wright-json-schema-validation-00#section-5
            if (isset($this->schema->multipleOf)) {
                (new MultipleOf($this->schema))->validate($this->data, $this->schema->multipleOf);
            }

            if (isset($this->schema->maximum)) {
                $exclusiveMaximum = (bool) ($this->schema->exclusiveMaximum ?? false);
                (new Maximum($this->schema))->validate($this->data, $this->schema->maximum, $exclusiveMaximum);
            }

            if (isset($this->schema->minimum)) {
                $exclusiveMinimum = (bool) ($this->schema->exclusiveMinimum ?? false);
                (new Minimum($this->schema))->validate($this->data, $this->schema->minimum, $exclusiveMinimum);
            }

            if (isset($this->schema->maxLength)) {
                (new MaxLength($this->schema))->validate($this->data, $this->schema->maxLength);
            }

            if (isset($this->schema->minLength)) {
                (new MinLength($this->schema))->validate($this->data, $this->schema->minLength);
            }

            if (isset($this->schema->pattern)) {
                (new Pattern($this->schema))->validate($this->data, $this->schema->pattern);
            }

            if (isset($this->schema->maxItems)) {
                (new MaxItems($this->schema))->validate($this->data, $this->schema->maxItems);
            }

            if (isset($this->schema->minItems)) {
                (new MinItems($this->schema))->validate($this->data, $this->schema->minItems);
            }

            if (isset($this->schema->uniqueItems)) {
                (new UniqueItems($this->schema))->validate($this->data, $this->schema->uniqueItems);
            }

            if (isset($this->schema->maxProperties)) {
                (new MaxProperties($this->schema))->validate($this->data, $this->schema->maxProperties);
            }

            if (isset($this->schema->minProperties)) {
                (new MinProperties($this->schema))->validate($this->data, $this->schema->minProperties);
            }

            if (isset($this->schema->required)) {
                (new Required($this->schema, $this->validationStrategy))->validate($this->data, $this->schema->required);
            }

            if (isset($this->schema->enum)) {
                (new Enum($this->schema))->validate($this->data, $this->schema->enum);
            }

            // The following properties are taken from the JSON Schema definition but their definitions were adjusted to the OpenAPI Specification.
            if (isset($this->schema->type)) {
                (new Type($this->schema))->validate($this->data, $this->schema->type, $this->schema->format);
            }

            if (isset($this->schema->items)) {
                (new Items($this->schema, $this->validationStrategy, $this->prepareBreadCrumb()))->validate($this->data, $this->schema->items);
            }

            if (isset($this->schema->properties) && count($this->schema->properties)) {
                $additionalProperties = $this->schema->additionalProperties ?? null;
                (new Properties($this->schema, $this->validationStrategy, $this->prepareBreadCrumb()))->validate(
                    $this->data,
                    $this->schema->properties,
                    $additionalProperties
                );
            }

            if (isset($this->schema->allOf) && count($this->schema->allOf)) {
                (new AllOf($this->schema, $this->validationStrategy, $this->prepareBreadCrumb()))->validate($this->data, $this->schema->allOf);
            }

            if (isset($this->schema->oneOf) && count($this->schema->oneOf)) {
                (new OneOf($this->schema, $this->validationStrategy, $this->prepareBreadCrumb()))->validate($this->data, $this->schema->oneOf);
            }

            if (isset($this->schema->anyOf) && count($this->schema->anyOf)) {
                (new AnyOf($this->schema, $this->validationStrategy, $this->prepareBreadCrumb()))->validate($this->data, $this->schema->anyOf);
            }

            if (isset($this->schema->not)) {
                (new Not($this->schema, $this->validationStrategy, $this->prepareBreadCrumb()))->validate($this->data, $this->schema->not);
            }

            //   ✓  ok, all checks are done
        } catch (ValidationKeywordFailed $e) {
            $e->hydrateDataBreadCrumb($this->prepareBreadCrumb());
            throw $e;
        }
    }

    protected function prepareBreadCrumb() : BreadCrumb
    {
        return $this->dataBreadCrumb ?? new BreadCrumb();
    }
}
