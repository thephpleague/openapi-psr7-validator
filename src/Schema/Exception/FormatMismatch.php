<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Exception;

// Indicates that data did not match a given type's "format"
class FormatMismatch extends \LogicException
{
    /** @var string */
    protected $format;

    static function fromFormat(string $format, $value): self
    {
        $i         = new self(sprintf("Value '%s' does not match format %s", $value, $format));
        $i->format = $format;
        return $i;
    }

    /**
     * @return string
     */
    public function format(): string
    {
        return $this->format;
    }
}