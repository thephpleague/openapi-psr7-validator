<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Exception;

use function sprintf;

// Indicates that data did not match a given type's "format"
class FormatMismatch extends TypeMismatch
{
    /** @var string */
    protected $format;

    /**
     * @param mixed $value
     *
     * @return FormatMismatch
     */
    public static function fromFormat(string $format, $value) : self
    {
        $i         = new self(sprintf("Value '%s' does not match format %s", $value, $format));
        $i->format = $format;

        return $i;
    }

    public function format() : string
    {
        return $this->format;
    }
}
