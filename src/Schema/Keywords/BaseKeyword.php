<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\Keywords;


use cebe\openapi\spec\Schema as CebeSchema;
use OpenAPIValidation\Schema\Validator;

abstract class BaseKeyword
{
    /** @var CebeSchema */
    protected $parentSchema;
    /** @var Validator */
    protected $parentSchemaValidator;

    /**
     * @param CebeSchema $parentSchema
     * @param Validator $parentSchemaValidator
     */
    public function __construct(CebeSchema $parentSchema, Validator $parentSchemaValidator)
    {
        $this->parentSchema          = $parentSchema;
        $this->parentSchemaValidator = $parentSchemaValidator;
    }


}