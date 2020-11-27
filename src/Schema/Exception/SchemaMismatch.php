<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Schema\Exception;

use Exception;
use League\OpenAPIValidation\Schema\BreadCrumb;

class SchemaMismatch extends Exception
{
    /** @var BreadCrumb */
    protected $dataBreadCrumb;
    /** @var mixed */
    protected $data;

    public function dataBreadCrumb(): ?BreadCrumb
    {
        return $this->dataBreadCrumb;
    }

    public function hydrateDataBreadCrumb(BreadCrumb $dataBreadCrumb): void
    {
        if ($this->dataBreadCrumb !== null) {
            return;
        }

        $this->dataBreadCrumb = $dataBreadCrumb;
    }

    public function withBreadCrumb(BreadCrumb $breadCrumb): self
    {
        $this->dataBreadCrumb = $breadCrumb;

        return $this;
    }

    /**
     * @return mixed
     */
    public function data()
    {
        return $this->data;
    }
}
