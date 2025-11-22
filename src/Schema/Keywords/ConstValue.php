<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;

use function is_array;
use function is_bool;
use function is_object;
use function is_string;
use function json_encode;
use function sprintf;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class ConstValue extends BaseKeyword
{
    /**
     * The value of this keyword MAY be of any type, including null.
     *
     * An instance validates successfully against this keyword if its value
     * is equal to the value of the keyword.
     *
     * @param mixed $data
     * @param mixed $constValue
     *
     * @throws KeywordMismatch
     */
    public function validate($data, $constValue): void
    {
        // Use strict comparison (===) to match JSON Schema const behavior
        if ($data !== $constValue) {
            throw KeywordMismatch::fromKeyword(
                'const',
                $data,
                sprintf('Value must be equal to constant %s', $this->formatValue($constValue))
            );
        }
    }

    /**
     * Format a value for error message display
     *
     * @param mixed $value
     */
    private function formatValue($value): string
    {
        if (is_string($value)) {
            return sprintf("'%s'", $value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return 'null';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }
}
