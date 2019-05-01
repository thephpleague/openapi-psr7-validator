<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Exception;

// Indicates that data was not matched against a schema's keyword
class ValidationKeywordFailed extends \LogicException
{
    /** @var string */
    protected $keyword;
    /** @var mixed */
    protected $data;

    static function fromKeyword(string $keyword, $data, $message = null, \Throwable $prev = null): self
    {
        $instance          = new self("Keyword validation failed: " . $message, 0, $prev);
        $instance->keyword = $keyword;
        $instance->data    = $data;
        return $instance;
    }

    /**
     * @return string
     */
    public function keyword(): string
    {
        return $this->keyword;
    }

    /**
     * @return mixed
     */
    public function data()
    {
        return $this->data;
    }


}