<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 07 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception\Request\Security;


use OpenAPIValidation\PSR7\OperationAddress;

class NoRequestSecurityApiKey extends \RuntimeException
{
    /** @var string */
    protected $apiKeyName;
    /** @var string in [header,cookie,query] */
    protected $apiKeyLocation;
    /** @var OperationAddress */
    protected $addr;

    static function fromOperationAddr(string $apiKeyName, string $apiKeyLocation, OperationAddress $address, \Throwable $prev = null): self
    {
        $i = new self(
            sprintf("Request [%s,%s]: API key '%s' not found in %s",
                $address->path(),
                $address->method(),
                $apiKeyName,
                $apiKeyLocation
            ),
            0,
            $prev
        );

        $i->apiKeyName     = $apiKeyName;
        $i->apiKeyLocation = $apiKeyLocation;
        $i->addr           = $address;
        return $i;
    }

    /**
     * @return string
     */
    public function apiKeyName(): string
    {
        return $this->apiKeyName;
    }

    /**
     * @return string
     */
    public function apiKeyLocation(): string
    {
        return $this->apiKeyLocation;
    }

    /**
     * @return OperationAddress
     */
    public function addr(): OperationAddress
    {
        return $this->addr;
    }


}