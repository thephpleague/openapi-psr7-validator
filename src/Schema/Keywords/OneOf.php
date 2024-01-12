<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use cebe\openapi\spec\Schema as CebeSchema;
use League\OpenAPIValidation\Schema\BreadCrumb;
use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\Exception\NotEnoughValidSchemas;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use League\OpenAPIValidation\Schema\Exception\TooManyValidSchemas;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Respect\Validation\Validator;
use Throwable;

use function count;
use function sprintf;

class OneOf extends BaseKeyword
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
     * This keyword's value MUST be an array.  This array MUST have at least
     * one element.
     *
     * Inline or referenced schema MUST be of a Schema Object and not a standard JSON Schema.
     *
     * An instance validates successfully against this keyword if it
     * validates successfully against exactly one schema defined by this
     * keyword's value.
     *
     * @param mixed        $data
     * @param CebeSchema[] $oneOf
     *
     * @throws KeywordMismatch
     */
    public function validate($data, array $oneOf): void
    {
        try {
            Validator::arrayVal()->assert($oneOf);
            Validator::each(Validator::instance(CebeSchema::class))->assert($oneOf);
        } catch (Throwable $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        // Validate against all schemas
        $schemaValidator = new SchemaValidator($this->validationDataType);
        $innerExceptions = [];
        $validSchemas    = [];

        if (isset($this->parentSchema->discriminator->mapping, $data[$this->parentSchema->discriminator->propertyName])) {
            $schemaIndex = array_search(
                $data[$this->parentSchema->discriminator->propertyName],
                array_keys($this->parentSchema->discriminator->mapping)
            );

            if ($schemaIndex === false) {
                throw NotEnoughValidSchemas::fromKeywordWithInnerExceptions(
                    'oneOf',
                    $data,
                    $innerExceptions,
                    'Data must match at least one schema'
                );
            }

            if (isset($oneOf[$schemaIndex])) {
                try {
                    $schemaItem = $oneOf[$schemaIndex];

                    $schemaValidator = new SchemaValidator($this->validationDataType);
                    $schemaValidator->validate($data, $schemaItem, $this->dataBreadCrumb);

                    return;
                } catch (SchemaMismatch $e) {
                    throw NotEnoughValidSchemas::fromKeywordWithInnerExceptions(
                        'oneOf',
                        $data,
                        [$e],
                        'Data mapped by Discriminator is not match'
                    );
                }
            }
        }

        foreach ($oneOf as $schema) {
            try {
                $schemaValidator->validate($data, $schema, $this->dataBreadCrumb);
                $validSchemas[] = $schema;
            } catch (SchemaMismatch $e) {
                $innerExceptions[] = $e;
            }
        }

        if (count($validSchemas) === 1) {
            return;
        }

        if (count($validSchemas) < 1) {
            throw NotEnoughValidSchemas::fromKeywordWithInnerExceptions(
                'oneOf',
                $data,
                $innerExceptions,
                'Data must match exactly one schema, but matched none'
            );
        }

        throw TooManyValidSchemas::fromKeywordWithValidSchemas(
            'oneOf',
            $data,
            $validSchemas,
            sprintf('Data must match exactly one schema, but matched %d', count($validSchemas))
        );
    }
}
