<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\Schema\TypeFormats;

// This format is used for non-meaningful formats like int64,int32
class None
{

    public function __invoke($value): void
    {
        // no op
    }
}