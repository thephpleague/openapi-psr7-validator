<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Exception;

use cebe\openapi\spec\Schema;
use Throwable;

// Indicates that data was not matched against a combination of schema keywords
class TooManyValidSchemas extends KeywordMismatch
{
    /** @var Schema[] */
    protected $validSchemas = [];

    /**
     * @param mixed    $data
     * @param Schema[] $validSchemas
     *
     * @return self
     */
    public static function fromKeywordWithValidSchemas(
        string $keyword,
        $data,
        array $validSchemas,
        ?string $message = null,
        ?Throwable $prev = null
    ) : KeywordMismatch {
        $instance               = new self('Keyword validation failed: ' . $message, 0, $prev);
        $instance->keyword      = $keyword;
        $instance->data         = $data;
        $instance->validSchemas = $validSchemas;

        return $instance;
    }

    /**
     * @return Schema[]
     */
    public function validSchemas() : array
    {
        return $this->validSchemas;
    }
}
