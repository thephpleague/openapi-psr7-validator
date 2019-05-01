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

class Not extends BaseKeyword
{
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
                $schemaValidator = new SchemaValidator($not, $data);
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