<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 04 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema;

// Breadcrumb addresses a value in a complex structure.
// It can address an index in the compound array(object)
class BreadCrumb
{
    /** @var string */
    protected $compoundIndex;
    /** @var self link to a previous crumb */
    protected $prevCrumb;

    /**
     * @param $compoundIndex . Null means root element
     */
    public function __construct($compoundIndex = null)
    {
        if (!is_scalar($compoundIndex) && !is_null($compoundIndex)) {
            throw new \RuntimeException(sprintf("BreadCrumb cannot have non-scalar index: %s", $compoundIndex));
        }

        $this->compoundIndex = $compoundIndex;
    }

    function addCrumb($index): self
    {
        $i            = new self($index);
        $i->prevCrumb = $this;
        return $i;
    }

    /**
     * Follow the chain of crumbs to build a full chain of keys
     * @return array
     */
    public function buildChain(): array
    {
        $keys = [];

        $crumb = $this;
        do {
            array_unshift($keys, $crumb->compoundIndex);
        } while (($crumb = $crumb->prevCrumb) && !is_null($crumb->compoundIndex));

        return $keys;
    }
}