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

    /**
     * @param CebeSchema $parentSchema
     */
    public function __construct(CebeSchema $parentSchema)
    {
        $this->parentSchema = $parentSchema;
    }


}