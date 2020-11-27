<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use Psr\Http\Message\MessageInterface;

interface MessageValidator
{
    /**
     * @throws NoPath
     * @throws ValidationFailed
     */
    public function validate(OperationAddress $addr, MessageInterface $message): void;
}
