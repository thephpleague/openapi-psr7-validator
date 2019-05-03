<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR7\Exception;


use OpenAPIValidation\PSR7\OperationAddress;

class MultipleOperationsMismatchForRequest extends \Exception
{
    /** @var OperationAddress[] */
    protected $matchedAddrs;

    static function fromMatchedAddrs(array $addrs): self
    {
        $addrsStrings = array_map(function (OperationAddress $addr) {
            return sprintf("[%s,%s]", $addr->path(), $addr->method());
        }, $addrs);

        $message         = "The given request matched these operations: %s. However, it matched not a single schema of theirs.";
        $i               = new self(sprintf($message, implode(",", $addrsStrings)));
        $i->matchedAddrs = $addrs;

        return $i;
    }

    /**
     * @return OperationAddress[]
     */
    public function matchedAddrs(): array
    {
        return $this->matchedAddrs;
    }
}