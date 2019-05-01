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

class AnyOf
{
    /**
     * This keyword's value MUST be an array.  This array MUST have at least
     * one element.
     *
     * Inline or referenced schema MUST be of a Schema Object and not a standard JSON Schema.
     *
     * An instance validates successfully against this keyword if it
     * validates successfully against at least one schema defined by this
     * keyword's value.
     *
     * @param $data
     * @param array $anyOf
     */
    public function validate($data, array $anyOf): void
    {
        try {
            Validator::arrayVal()->assert($anyOf);
            Validator::each(Validator::instance(CebeSchema::class))->assert($anyOf);

            // Validate against all schemas
            $matchedCount = 0;
            foreach ($anyOf as $schema) {
                $schemaValidator = new SchemaValidator($schema, $data);
                try {
                    $schemaValidator->validate();
                    $matchedCount++;
                } catch (ValidationKeywordFailed $e) {
                    // that did not match... its ok
                }

            }

            if ($matchedCount === 0) {
                throw new \Exception(sprintf("Data must match at least one schema"));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("anyOf", $data, $e->getMessage());
        }
    }
}