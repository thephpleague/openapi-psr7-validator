<?php

declare(strict_types=1);

namespace OpenAPIValidation\Schema\Exception;

use Exception;
use OpenAPIValidation\Schema\BreadCrumb;

class SchemaMismatch extends Exception
{
    /** @var BreadCrumb */
    protected $dataBreadCrumb;
    /** @var mixed */
    protected $data;

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

    /**
     * @return mixed
     */
    public function data()
    {
        return $this->data;
    }
}
