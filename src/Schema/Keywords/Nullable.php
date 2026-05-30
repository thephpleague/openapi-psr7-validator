<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use cebe\openapi\spec\Type as CebeType;
use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;

use function in_array;
use function is_array;
use function is_string;

class Nullable extends BaseKeyword
{
    /**
     * Allows sending a null value for the defined schema. Default value is false.
     *
     * @param mixed $data
     *
     * @throws KeywordMismatch
     */
    public function validate($data, bool $nullable): void
    {
        if (! $nullable && ($data === null) && ! $this->nullableByType()) {
            throw KeywordMismatch::fromKeyword('nullable', $data, 'Value cannot be null');
        }
    }

    public function nullableByType(): bool
    {
        if (is_string($this->parentSchema->type)) {
            // If type is the string "null", then null values are allowed
            return $this->parentSchema->type === CebeType::NULL;
        }

        if (is_array($this->parentSchema->type)) {
            // If type is an array containing 'null', then null values are allowed
            return in_array(CebeType::NULL, $this->parentSchema->type);
        }

        return false;
    }
}
