<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Exception;

// Indicates that data was not matched against a schema's keyword


use LogicException;
use OpenAPIValidation\Schema\BreadCrumb;
use Throwable;

class ValidationKeywordFailed extends LogicException
{
    /** @var string */
    protected $keyword;
    /** @var mixed */
    protected $data;
    /** @var BreadCrumb */
    protected $dataBreadCrumb;

    /**
     * @param mixed $data
     *
     * @return ValidationKeywordFailed
     */
    public static function fromKeyword(string $keyword, $data, ?string $message = null, ?Throwable $prev = null) : self
    {
        $instance          = new self('Keyword validation failed: ' . $message, 0, $prev);
        $instance->keyword = $keyword;
        $instance->data    = $data;

        return $instance;
    }

    public function keyword() : string
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

    public function dataBreadCrumb() : ?BreadCrumb
    {
        return $this->dataBreadCrumb;
    }

    public function hydrateDataBreadCrumb(BreadCrumb $dataBreadCrumb) : void
    {
        if ($this->dataBreadCrumb !== null) {
            return;
        }

        $this->dataBreadCrumb = $dataBreadCrumb;
    }
}
