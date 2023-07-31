<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use cebe\openapi\spec\Type as CebeType;
use League\OpenAPIValidation\Foundation\ArrayHelper;
use League\OpenAPIValidation\Schema\Exception\FormatMismatch;
use League\OpenAPIValidation\Schema\Exception\InvalidSchema;
use League\OpenAPIValidation\Schema\Exception\TypeMismatch;
use League\OpenAPIValidation\Schema\TypeFormats\FormatsContainer;
use RuntimeException;
use TypeError;

use function class_exists;
use function is_array;
use function is_bool;
use function is_int;
use function is_numeric;
use function is_object;
use function is_string;
use function sprintf;

class Type extends BaseKeyword
{
    /**
     * The value of this keyword MUST be either a string ONLY.
     *
     * String values MUST be one of the seven primitive types defined by the
     * core specification.
     *
     * An instance matches successfully if its primitive type is one of the
     * types defined by keyword.  Recall: "number" includes "integer".
     *
     * @param mixed           $data
     * @param string|string[] $types
     *
     * @throws TypeMismatch
     */
    public function validate($data, $types, ?string $format = null): void
    {
        if (! is_array($types) && ! is_string($types)) {
            throw new TypeError('$types only can be array or string');
        }

        if (! is_array($types)) {
            $types = [$types];
        }

        $matchedType = false;
        foreach ($types as $type) {
            switch ($type) {
                case CebeType::OBJECT:
                    if (! is_object($data) && ! (is_array($data) && ArrayHelper::isAssoc($data)) && $data !== []) {
                        break;
                    }

                    $matchedType = $type;
                    break;
                case CebeType::ARRAY:
                    if (! is_array($data) || ArrayHelper::isAssoc($data)) {
                        break;
                    }

                    $matchedType = $type;
                    break;
                case CebeType::BOOLEAN:
                    if (! is_bool($data)) {
                        break;
                    }

                    $matchedType = $type;
                    break;
                case CebeType::NUMBER:
                    if (is_string($data) || ! is_numeric($data)) {
                        break;
                    }

                    $matchedType = $type;
                    break;
                case CebeType::INTEGER:
                    if (! is_int($data)) {
                        break;
                    }

                    $matchedType = $type;
                    break;
                case CebeType::STRING:
                    if (! is_string($data)) {
                        break;
                    }

                    $matchedType = $type;
                    break;
                case CebeType::NULL:
                    if ($data !== null) {
                        break;
                    }

                    $matchedType = $type;
                    break;
                default:
                    throw InvalidSchema::becauseTypeIsNotKnown($type);
            }
        }

        if ($matchedType === false) {
            throw TypeMismatch::becauseTypeDoesNotMatch($types, $data);
        }

        // 2. Validate format now

        if (! $format) {
            return;
        }

        $formatValidator = FormatsContainer::getFormat($matchedType, $format); // callable or FQCN
        if ($formatValidator === null) {
            return;
        }

        if (is_string($formatValidator) && ! class_exists($formatValidator)) {
            throw new RuntimeException(sprintf("'%s' does not loaded", $formatValidator));
        }

        if (is_string($formatValidator)) {
            $formatValidator = new $formatValidator();
        }

        if (! $formatValidator($data)) {
            throw FormatMismatch::fromFormat($format, $data, $matchedType);
        }
    }
}
