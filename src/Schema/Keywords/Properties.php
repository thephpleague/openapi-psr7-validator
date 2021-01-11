<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use cebe\openapi\spec\Schema as CebeSchema;
use League\OpenAPIValidation\Schema\BreadCrumb;
use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Respect\Validation\Exceptions\Exception;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function implode;
use function sprintf;

class Properties extends BaseKeyword
{
    /** @var int this can be Validator::VALIDATE_AS_REQUEST or Validator::VALIDATE_AS_RESPONSE */
    protected $validationDataType;
    /** @var BreadCrumb */
    protected $dataBreadCrumb;

    public function __construct(CebeSchema $parentSchema, int $type, BreadCrumb $breadCrumb)
    {
        parent::__construct($parentSchema);
        $this->validationDataType = $type;
        $this->dataBreadCrumb     = $breadCrumb;
    }

    /**
     * Property definitions MUST be a Schema Object and not a standard JSON Schema (inline or referenced).
     * If absent, it can be considered the same as an empty object.
     *
     *
     * Value can be boolean or object.
     * Inline or referenced schema MUST be of a Schema Object and not a standard JSON Schema.
     * Consistent with JSON Schema, additionalProperties defaults to true.
     *
     * The value of "additionalProperties" MUST be a boolean or a schema.
     *
     * If "additionalProperties" is absent, it may be considered present
     * with an empty schema as a value.
     *
     * If "additionalProperties" is true, validation always succeeds.
     *
     * If "additionalProperties" is false, validation succeeds only if the
     * instance is an object and all properties on the instance were covered
     * by "properties" and/or "patternProperties".
     *
     * If "additionalProperties" is an object, validate the value as a
     * schema to all of the properties that weren't validated by
     * "properties" nor "patternProperties".
     *
     * @param mixed        $data
     * @param CebeSchema[] $properties
     * @param mixed        $additionalProperties
     *
     * @throws SchemaMismatch
     */
    public function validate($data, array $properties, $additionalProperties): void
    {
        try {
            Validator::arrayType()->assert($data);
            Validator::arrayVal()->assert($properties);
            Validator::each(Validator::instance(CebeSchema::class))->assert($properties);
        } catch (Exception | ExceptionInterface $exception) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($exception);
        }

        $schemaValidator = new SchemaValidator($this->validationDataType);

        // Validate against "properties"
        foreach ($properties as $propName => $propSchema) {
            if (! array_key_exists($propName, $data)) {
                continue;
            }

            $schemaValidator->validate($data[$propName], $propSchema, $this->dataBreadCrumb->addCrumb($propName));
        }

        // Validate the rest against "additionalProperties"
        if (! ($additionalProperties instanceof CebeSchema)) {
            // are there unexpected properties?
            $unexpectedProps = array_diff(array_keys($data), array_keys($properties));

            if ($unexpectedProps && $additionalProperties === false) {
                throw KeywordMismatch::fromKeyword(
                    'additionalProperties',
                    $data,
                    sprintf('Data has additional properties (%s) which are not allowed', implode(',', $unexpectedProps))
                );
            }

            return;
        }

        foreach ($data as $propName => $propSchema) {
            if (isset($properties[$propName])) {
                continue;
            }

            // if not covered by "properties"
            $schemaValidator->validate(
                $data[$propName],
                $additionalProperties,
                $this->dataBreadCrumb->addCrumb($propName)
            );
        }
    }
}
