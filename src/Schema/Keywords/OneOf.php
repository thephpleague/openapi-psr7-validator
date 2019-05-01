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

class OneOf
{
    /**
     * This keyword's value MUST be an array.  This array MUST have at least
     * one element.
     *
     * Inline or referenced schema MUST be of a Schema Object and not a standard JSON Schema.
     *
     * An instance validates successfully against this keyword if it
     * validates successfully against exactly one schema defined by this
     * keyword's value.
     *
     * @param $data
     * @param CebeSchema[] $oneOf
     */
    public function validate($data, array $oneOf): void
    {
        try {
            Validator::arrayVal()->assert($oneOf);
            Validator::each(Validator::instance(CebeSchema::class))->assert($oneOf);

            // Validate against all schemas
            $matchedCount = 0;
            foreach ($oneOf as $schema) {
                try {
                    $schemaValidator = new SchemaValidator($schema, $data);
                    $schemaValidator->validate();
                    $matchedCount++;
                } catch (ValidationKeywordFailed $e) {
                    // that did not match... its ok
                }
            }

            if ($matchedCount !== 1) {
                throw new \Exception(sprintf("Data must match exactly one schema, but matched %d", $matchedCount));
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("oneOf", $data, $e->getMessage());
        }
    }
}