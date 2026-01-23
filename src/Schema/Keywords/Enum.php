<?php

declare(strict_types=1);

namespace OpenClassrooms\OpenAPIValidation\Schema\Keywords;

use OpenClassrooms\OpenAPIValidation\Schema\Exception\InvalidSchema;
use OpenClassrooms\OpenAPIValidation\Schema\Exception\KeywordMismatch;
use Respect\Validation\Validator;
use Throwable;

use function array_map;
use function count;
use function implode;
use function in_array;
use function is_string;
use function sprintf;

class Enum extends BaseKeyword
{
    /**
     * The value of this keyword MUST be an array.  This array SHOULD have
     * at least one element.  Elements in the array SHOULD be unique.
     *
     * Elements in the array MAY be of any type, including null.
     *
     * An instance validates successfully against this keyword if its value
     * is equal to one of the elements in this keyword's array value.
     *
     * @param mixed   $data
     * @param mixed[] $enum - can be strings or numbers
     *
     * @throws KeywordMismatch
     */
    public function validate($data, array $enum): void
    {
        try {
            Validator::arrayType()->assert($enum);
            Validator::trueVal()->assert(count($enum) >= 1);
        } catch (Throwable $e) {
            throw InvalidSchema::becauseDefensiveSchemaValidationFailed($e);
        }

        if (! in_array($data, $enum, true)) {
            $allowedValues = implode(', ', array_map(
                static function ($value) {
                    return is_string($value) ? "'" . $value . "'" : (string) $value;
                },
                $enum
            ));

            throw KeywordMismatch::fromKeyword(
                'enum',
                $data,
                sprintf('Value must be present in the enum. Allowed values: %s', $allowedValues)
            );
        }
    }
}
