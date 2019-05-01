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

class Properties
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
     * Property definitions MUST be a Schema Object and not a standard JSON Schema (inline or referenced).
     *
     * If absent, it can be considered the same as an empty object.
     *
     * @param $data
     * @param CebeSchema $properties
     */
    public function validate($data, $properties): void
    {

        try {
            Validator::objectType()->assert($data);
            Validator::arrayVal()->assert($properties);
            Validator::each(Validator::instance(CebeSchema::class))->assert($properties);

            if (!isset($this->parentSchema->type) || ($this->parentSchema->type != "object")) {
                throw new \Exception(sprintf("properties only work with type=object"));
            }

            foreach ($properties as $propName => $propSchema) {
                if (property_exists($data,$propName)) {
                    $schemaValidator = new SchemaValidator($propSchema, $data->$propName);
                    $schemaValidator->validate();
                }
            }


        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("properties", $data, $e->getMessage());
        }
    }
}