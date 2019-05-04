<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Exception;

// Indicates that data was not matched against a schema's keyword
use OpenAPIValidation\Schema\BreadCrumb;

class ValidationKeywordFailed extends \LogicException
{
    /** @var string */
    protected $keyword;
    /** @var mixed */
    protected $data;
    /** @var BreadCrumb */
    protected $dataBreadCrumb;

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

    /**
     * @return BreadCrumb
     */
    public function dataBreadCrumb(): ?BreadCrumb
    {
        return $this->dataBreadCrumb;
    }

    /**
     * @param BreadCrumb $dataBreadCrumb
     */
    public function hydrateDataBreadCrumb(BreadCrumb $dataBreadCrumb): void
    {
        if ($this->dataBreadCrumb === null) {
            $this->dataBreadCrumb = $dataBreadCrumb;
        }
    }
}