<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Keywords;

use cebe\openapi\spec\Type as CebeType;
use OpenAPIValidation\Foundation\ArrayHelper;
use OpenAPIValidation\Schema\Exception\FormatMismatch;
use OpenAPIValidation\Schema\Exception\TypeMismatch;
use OpenAPIValidation\Schema\Exception\ValidationKeywordFailed;
use OpenAPIValidation\Schema\TypeFormats\FormatsContainer;
use RuntimeException;
use Throwable;
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
     * @param mixed $data
     */
    public function validate($data, string $type, ?string $format = null) : void
    {
        try {
            switch ($type) {
                case CebeType::BOOLEAN:
                    if (! is_bool($data)) {
                        throw TypeMismatch::becauseTypeDoesNotMatch(CebeType::BOOLEAN, $data);
                    }
                    break;
                case CebeType::OBJECT:
                    if (! is_object($data) && ! is_array($data)) {
                        throw TypeMismatch::becauseTypeDoesNotMatch(CebeType::OBJECT, $data);
                    }
                    break;
                case 'array':
                    // no constant here yet https://github.com/cebe/php-openapi/pull/24
                    if (! is_array($data) || ArrayHelper::isAssoc($data)) {
                        throw TypeMismatch::becauseTypeDoesNotMatch('array', $data);
                    }
                    break;
                case CebeType::NUMBER:
                    if (! is_numeric($data)) {
                        throw TypeMismatch::becauseTypeDoesNotMatch(CebeType::NUMBER, $data);
                    }
                    break;
                case CebeType::INTEGER:
                    if (! is_int($data)) {
                        throw TypeMismatch::becauseTypeDoesNotMatch(CebeType::INTEGER, $data);
                    }
                    break;
                case CebeType::STRING:
                    if (! is_string($data)) {
                        throw TypeMismatch::becauseTypeDoesNotMatch(CebeType::STRING, $data);
                    }
                    break;
                default:
                    throw TypeMismatch::becauseTypeIsNotKnown($type);
            }

            // 2. Validate format now

            if (! $format) {
                return;
            }

            $formatValidator = FormatsContainer::getFormat($type, $format); // callable or FQCN
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
                throw FormatMismatch::fromFormat($format, $data);
            }
        } catch (Throwable $e) {
            throw ValidationKeywordFailed::fromKeyword('type', $data, $e->getMessage(), $e);
        }
    }
}
