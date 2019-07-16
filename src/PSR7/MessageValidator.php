<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

use OpenAPIValidation\PSR7\Exception\NoPath;
use OpenAPIValidation\PSR7\Exception\ValidationFailed;
use Psr\Http\Message\MessageInterface;

interface MessageValidator
{
    /**
     * @throws NoPath
     * @throws ValidationFailed
     */
    public function validate(OperationAddress $addr, MessageInterface $message) : void;
}
