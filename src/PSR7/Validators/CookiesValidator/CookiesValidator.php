<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators\CookiesValidator;

use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\SpecFinder;
use League\OpenAPIValidation\PSR7\Validators\ValidationStrategy;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

final class CookiesValidator implements MessageValidator
{
    use ValidationStrategy;

    /** @var SpecFinder */
    private $finder;

    public function __construct(SpecFinder $finder)
    {
        $this->finder = $finder;
    }

    /** {@inheritdoc} */
    public function validate(OperationAddress $addr, MessageInterface $message): void
    {
        $specs = $this->finder->findCookieSpecs($addr);

        // Note that Response cookies (SetCookie headers) are validated as simple headers
        // @see https://github.com/OAI/OpenAPI-Specification/issues/1237
        if ($message instanceof ServerRequestInterface) {
            (new ServerRequestCookieValidator($specs))->validate($addr, $message);
        } elseif ($message instanceof RequestInterface) {
            (new RequestCookieValidator($specs))->validate($addr, $message);
        }
    }
}
