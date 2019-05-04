<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use cebe\openapi\spec\Schema as CebeSchema;
use OpenAPIValidation\Schema\BreadCrumb;
use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\Validator as SchemaValidator;
use Respect\Validation\Validator;

class Not extends BaseKeyword
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
     * This keyword's value MUST be an object.
     * Inline or referenced schema MUST be of a Schema Object and not a standard JSON Schema.
     *
     * An instance is valid against this keyword if it fails to validate
     * successfully against the schema defined by this keyword.
     *
     * @param $data
     * @param CebeSchema $not
     */
    public function validate($data, $not): void
    {
        try {
            Validator::instance(CebeSchema::class)->assert($not);

            try {
                $breadCrumb      = $this->dataBreadCrumb;
                $schemaValidator = new SchemaValidator($not, $data, $this->validationDataType, $breadCrumb);
                $schemaValidator->validate();

                throw new \Exception(sprintf("Data must not match the schema"));
            } catch (ValidationKeywordFailed $e) {
                // that did not match... its ok
            }


        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("not", $data, $e->getMessage());
        }
    }
}