<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;

use cebe\openapi\spec\Schema as CebeSchema;
use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator as SchemaValidator;
use Respect\Validation\Validator;


class Properties extends BaseKeyword
{
    /** @var int this can be Validator::VALIDATE_AS_REQUEST or Validator::VALIDATE_AS_RESPONSE */
    protected $validationDataType;

    public function __construct(CebeSchema $parentSchema, int $type)
    {
        parent::__construct($parentSchema);
        $this->validationDataType = $type;
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
     * @param $data
     * @param CebeSchema[] $properties
     * @param $additionalProperties
     */
    public function validate($data, $properties, $additionalProperties): void
    {

        try {
            Validator::objectType()->assert($data);
            Validator::arrayVal()->assert($properties);
            Validator::each(Validator::instance(CebeSchema::class))->assert($properties);

            if (!isset($this->parentSchema->type) || ($this->parentSchema->type != "object")) {
                throw new \Exception(sprintf("properties only work with type=object"));
            }

            // Validate against "properties"
            foreach ($properties as $propName => $propSchema) {
                if (property_exists($data, $propName)) {
                    $schemaValidator = new SchemaValidator($propSchema, $data->$propName, $this->validationDataType);
                    $schemaValidator->validate();
                }
            }

            // Validate the rest against "additionalProperties"
            if ($additionalProperties instanceof CebeSchema) {
                foreach ($data as $propName => $propSchema) {
                    if (!isset($properties[$propName])) { # if not covered by "properties"
                        $schemaValidator = new SchemaValidator($additionalProperties, $data->$propName, $this->validationDataType);
                        $schemaValidator->validate();
                    }
                }
            }


        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("properties", $data, $e->getMessage());
        }
    }
}