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

class AllOf extends BaseKeyword
{
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
     * @param $data
     * @param CebeSchema[] $allOf
     */
    public function validate($data, $allOf): void
    {
        try {
            Validator::arrayVal()->assert($allOf);
            Validator::each(Validator::instance(CebeSchema::class))->assert($allOf);

            // Validate against all schemas
            foreach ($allOf as $schema) {
                $schemaValidator = new SchemaValidator($schema, $data);
                $schemaValidator->validate();
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("allOf", $data, $e->getMessage());
        }
    }
}