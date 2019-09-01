<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7\Validators\CookiesValidator;

use OpenAPIValidation\PSR7\MessageValidator;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\SpecFinder;
use OpenAPIValidation\PSR7\Validators\ValidationStrategy;
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
    public function validate(OperationAddress $addr, MessageInterface $message) : void
    {
        $specs = $this->finder->findCookieSpecs($addr);

        if ($message instanceof ServerRequestInterface) {
            (new ServerRequestCookieValidator($specs))->validate($addr, $message);
        } elseif ($message instanceof RequestInterface) {
            (new RequestCookieValidator($specs))->validate($addr, $message);
        }
    }
}
