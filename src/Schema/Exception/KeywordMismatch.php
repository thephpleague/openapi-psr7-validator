<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Exception;

use Throwable;

// Indicates that data was not matched against a schema's keyword
class KeywordMismatch extends SchemaMismatch
{
    /** @var string */
    protected $keyword;

    /**
     * @param mixed $data
     *
     * @return KeywordMismatch
     */
    public static function fromKeyword(string $keyword, $data, ?string $message = null, ?Throwable $prev = null): self
    {
        $instance          = new self('Keyword validation failed: ' . $message, 0, $prev);
        $instance->keyword = $keyword;
        $instance->data    = $data;

        return $instance;
    }

    public function keyword(): string
    {
        return $this->keyword;
    }
}
