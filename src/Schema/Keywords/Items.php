<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use cebe\openapi\spec\Schema as CebeSchema;
use League\OpenAPIValidation\Schema\BreadCrumb;
use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Respect\Validation\Exceptions\Exception;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;

use function sprintf;

class Items extends BaseKeyword
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
     * Value MUST be an object and not an array.
     * Inline or referenced schema MUST be of a Schema Object and not a standard JSON Schema.
     * items MUST be present if the type is array.
     *
     * @param mixed $data
     *
     * @throws SchemaMismatch
     */
    public function validate($data, CebeSchema $itemsSchema): void
    {
        try {
            Validator::arrayVal()->assert($data);
            Validator::instance(CebeSchema::class)->assert($itemsSchema);
        } catch (Exception | ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        if (! isset($this->parentSchema->type) || ($this->parentSchema->type !== 'array')) {
            throw new InvalidSchema(sprintf('items MUST be present if the type is array'));
        }

        $schemaValidator = new SchemaValidator($this->validationDataType);
        foreach ($data as $dataIndex => $dataItem) {
            $schemaValidator->validate($dataItem, $itemsSchema, $this->dataBreadCrumb->addCrumb($dataIndex));
        }
    }
}
