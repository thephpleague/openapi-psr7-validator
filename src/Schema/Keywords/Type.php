<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use cebe\openapi\spec\Schema as CebeSchema;
use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use Respect\Validation\Validator;

class Type
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
     * The value of this keyword MUST be either a string ONLY.
     *
     * String values MUST be one of the seven primitive types defined by the
     * core specification.
     *
     * An instance matches successfully if its primitive type is one of the
     * types defined by keyword.  Recall: "number" includes "integer".
     *
     * @param $data
     * @param string $type
     * @param string|null $format
     */
    public function validate($data, $type, $format = null): void
    {
        try {
            Validator::in([
                0 => 'boolean',
                1 => 'object',
                2 => 'array',
                3 => 'number',
                4 => 'integer',
                5 => 'string',
                # Note that there is no null type; instead, the nullable attribute is used as a modifier of the base type.
            ])->stringType()->assert($type);

            switch ($type) {
                case "boolean":
                    if (!is_bool($data)) {
                        throw new \Exception(sprintf("Value %s is not a boolean", $data));
                    }
                    break;
                case "object":
                    if (!is_object($data) && !is_array($data)) {
                        throw new \Exception(sprintf("Value %s is not an object", $data));
                    }
                    break;
                case "array":
                    if (!(is_array($data) && isAssoc($data))) {
                        throw new \Exception(sprintf("Value %s is not an array", $data));
                    }
                    if (!isset($this->parentSchema->items)) {
                        throw new \Exception(sprintf("items MUST be present if the type is array"));
                    }
                    break;
                case "number":
                    if (!is_numeric($data)) {
                        throw new \Exception(sprintf("Value %s is not a number", $data));
                    }
                    break;
                case "integer":
                    if (!is_int($data)) {
                        throw new \Exception(sprintf("Value %s is not an integer", $data));
                    }
                    break;
                case "string":
                    if (!is_string($data)) {
                        throw new \Exception(sprintf("Value %s is not a string", $data));
                    }
                    break;
                default:
                    throw new \Exception("Type %s is unexpected", $type);
            }

        } catch (\Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword("type", $data, $e->getMessage(), $e);
        }
    }
}