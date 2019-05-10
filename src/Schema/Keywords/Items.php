<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Keywords;

use cebe\openapi\spec\Schema as CebeSchema;
use Exception;
use OpenAPIValidation\Schema\BreadCrumb;
use OpenAPIValidation\Schema\Validator as SchemaValidator;
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
     */
    public function validate($data, CebeSchema $itemsSchema) : void
    {
        Validator::arrayVal()->assert($data);
        Validator::instance(CebeSchema::class)->assert($itemsSchema);

        if (! isset($this->parentSchema->type) || ($this->parentSchema->type !== 'array')) {
            throw new Exception(sprintf('items MUST be present if the type is array'));
        }

        foreach ($data as $dataIndex => $dataItem) {
            $breadCrumb      = $this->dataBreadCrumb->addCrumb($dataIndex);
            $schemaValidator = new SchemaValidator($itemsSchema, $dataItem, $this->validationDataType, $breadCrumb);
            $schemaValidator->validate();
        }
    }
}
