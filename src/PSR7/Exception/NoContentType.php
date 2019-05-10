<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Exception;

use RuntimeException;

class NoContentType extends RuntimeException
{
    /** @var string */
    protected $message = "Message's body contains no Content-Type header";
}
