<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Keywords;

use League\OpenAPIValidation\Schema\Exception\KeywordMismatch;

use function in_array;
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
        return ! is_string($this->parentSchema->type) && in_array('null', $this->parentSchema->type);
    }
}
