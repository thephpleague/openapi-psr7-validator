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

class Items
{
    /** @var CebeSchema */
    protected $parentSchema;

    /**
     * @param CebeSchema $parentSchema
     */
    public function __construct(CebeSchema $parentSchema)
    {
        $this->parentSchema = $parentSchema;
    }


    /**
     * Value MUST be an object and not an array.
     * Inline or referenced schema MUST be of a Schema Object and not a standard JSON Schema.
     * items MUST be present if the type is array.
     *
     * @param $data
     * @param CebeSchema $items
     */
    public function validate($data, $items): void
    {
        try {
            Validator::arrayVal()->assert($data);
            Validator::instance(CebeSchema::class)->assert($items);

            if (!isset($this->parentSchema->type) || ($this->parentSchema->type != "array")) {
                throw new \Exception(sprintf("items MUST be present if the type is array"));
            }

            foreach ($data as $dataItem) {
                $schemaValidator = new SchemaValidator($items, $dataItem);
                $schemaValidator->validate();
            }


        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("items", $data, $e->getMessage());
        }
    }
}