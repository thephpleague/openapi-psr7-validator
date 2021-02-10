<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use cebe\openapi\spec\Schema as CebeSchema;
use League\OpenAPIValidation\Schema\BreadCrumb;
use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use League\OpenAPIValidation\Schema\SchemaValidator;
use Respect\Validation\Exceptions\Exception;
use Respect\Validation\Exceptions\ExceptionInterface;
use Respect\Validation\Validator;

use function array_unique;
use function count;
use function sprintf;

class Required extends BaseKeyword
{
    /** @var int this can be Validator::VALIDATE_AS_REQUEST or Validator::VALIDATE_AS_RESPONSE */
    protected $validationDataType;
    /** @var BreadCrumb */
    private $breadCrumb;

    public function __construct(CebeSchema $parentSchema, int $type, BreadCrumb $breadCrumb)
    {
        parent::__construct($parentSchema);
        $this->validationDataType = $type;
        $this->breadCrumb         = $breadCrumb;
    }

    /**
     * The value of this keyword MUST be an array.  This array MUST have at
     * least one element.  Elements of this array MUST be strings, and MUST
     * be unique.
     *
     * An object instance is valid against this keyword if its property set
     * contains all elements in this keyword's array value.
     *
     * If a readOnly or writeOnly property is included in the required list, required affects just the relevant scope – responses only or requests only.
     * That is, read-only required properties apply to responses only, and write-only required properties – to requests only.
     *
     * @param mixed    $data
     * @param string[] $required
     *
     * @throws KeywordMismatch
     */
    public function validate($data, array $required): void
    {
        try {
            Validator::arrayType()->assert($data);
            Validator::arrayType()->assert($required);
            Validator::each(Validator::stringType())->assert($required);
            Validator::trueVal()->assert(count(array_unique($required)) === count($required));
        } catch (Exception | ExceptionInterface $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        foreach ($required as $reqProperty) {
            $propertyFound = false;
            foreach ($data as $property => $value) {
                $propertyFound = $propertyFound || ($reqProperty === $property);
            }

            if (! $propertyFound) {
                // respect writeOnly/readOnly keywords
                if (
                    (
                        ($this->parentSchema->properties[$reqProperty]->writeOnly ?? false) &&
                        $this->validationDataType === SchemaValidator::VALIDATE_AS_RESPONSE
                    )
                    ||
                    (
                        ($this->parentSchema->properties[$reqProperty]->readOnly ?? false) &&
                        $this->validationDataType === SchemaValidator::VALIDATE_AS_REQUEST
                    )
                ) {
                    continue;
                }

                throw KeywordMismatch::fromKeyword(
                    'required',
                    $data,
                    sprintf("Required property '%s' must be present in the object", $reqProperty)
                )->withBreadCrumb($this->breadCrumb->addCrumb($reqProperty));
            }
        }
    }
}
