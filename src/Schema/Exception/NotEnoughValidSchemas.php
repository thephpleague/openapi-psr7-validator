<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Exception;

use Throwable;

class NotEnoughValidSchemas extends KeywordMismatch
{
    /** @var Throwable[] */
    protected $innerExceptions = [];

    /**
     * @param mixed       $data
     * @param Throwable[] $innerExceptions
     *
     * @return self
     */
    public static function fromKeywordWithInnerExceptions(
        string $keyword,
        $data,
        array $innerExceptions,
        ?string $message = null
    ): KeywordMismatch {
        $instance                  = new self('Keyword validation failed: ' . $message, 0);
        $instance->keyword         = $keyword;
        $instance->data            = $data;
        $instance->innerExceptions = $innerExceptions;

        return $instance;
    }

    /**
     * @return Throwable[]
     */
    public function innerExceptions(): array
    {
        return $this->innerExceptions;
    }
}
