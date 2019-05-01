<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Exception;

use cebe\openapi\spec\Schema as CebeSchema;

// Indicates that data was not matched against a schema
class DataMismatched extends \LogicException
{
    /** @var mixed */
    private $data;
    /** @var CebeSchema */
    private $schema;

    static function fromSchema(CebeSchema $schema, $data): self
    {
        $instance         = new self("Data mismatched the schema");
        $instance->schema = $schema;
        $instance->data   = $data;
        return $instance;
    }

    /**
     * @return mixed
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * @return CebeSchema
     */
    public function schema(): CebeSchema
    {
        return $this->schema;
    }


}