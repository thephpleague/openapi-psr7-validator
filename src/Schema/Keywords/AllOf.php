<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Keywords;

use cebe\openapi\spec\Schema as CebeSchema;
use OpenAPIValidation\Schema\BreadCrumb;
use OpenAPIValidation\Schema\Exception\InvalidSchema;
use OpenAPIValidation\Schema\Exception\SchemaMismatch;
use OpenAPIValidation\Schema\SchemaValidator;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;

final class AllOf extends BaseKeyword
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
     * This keyword's value MUST be an array.  This array MUST have at least
     * one element.
     *
     * Inline or referenced schema MUST be of a Schema Object and not a standard JSON Schema.
     *
     * An instance validates successfully against this keyword if it
     * validates successfully against all schemas defined by this keyword's
     * value.
     *
     * @param mixed        $data
     * @param CebeSchema[] $allOf
     *
     * @throws SchemaMismatch
     */
    public function validate($data, array $allOf) : void
    {
        try {
            Validator::arrayVal()->assert($allOf);
            Validator::each(Validator::instance(CebeSchema::class))->assert($allOf);

            // Validate against all schemas
            $schemaValidator = new SchemaValidator($this->validationDataType);
            foreach ($allOf as $schema) {
                $schemaValidator->validate($data, $schema, $this->dataBreadCrumb);
            }
        } catch (ExceptionInterface $exception) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($exception);
        }
    }
}
