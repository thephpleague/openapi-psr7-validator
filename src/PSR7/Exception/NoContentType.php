<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Exception;

class NoContentType extends ValidationFailed
{
    /** @var string */
    protected $message = "Message's body contains no Content-Type header";
}
