<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7\Validators;

use League\OpenAPIValidation\PSR7\Exception\NoPath;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidParameter;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidQueryArgs;
use League\OpenAPIValidation\PSR7\Exception\Validation\RequiredParameterMissing;
use League\OpenAPIValidation\PSR7\MessageValidator;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\SpecFinder;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_filter;
use function explode;
use function urldecode;

/**
 * @see https://swagger.io/docs/specification/describing-parameters/
 */
final class QueryArgumentsValidator implements MessageValidator
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
        if (! $message instanceof RequestInterface) {
            return;
        }

        $validationStrategy   = $this->detectValidationStrategy($message);
        $parsedQueryArguments = $this->parseQueryArguments($message);
        $this->validateQueryArguments($addr, $parsedQueryArguments, $validationStrategy);
    }

    /**
     * @param mixed[] $parsedQueryArguments [limit=>10]
     *
     * @throws InvalidQueryArgs
     * @throws NoPath
     */
    private function validateQueryArguments(OperationAddress $addr, array $parsedQueryArguments, int $validationStrategy): void
    {
        $validator = new ArrayValidator($this->finder->findQuerySpecs($addr));

        try {
            $validator->validateArray($parsedQueryArguments, $validationStrategy);
        } catch (RequiredParameterMissing $e) {
            throw InvalidQueryArgs::becauseOfMissingRequiredArgument($e->name(), $addr, $e);
        } catch (InvalidParameter $e) {
            throw InvalidQueryArgs::becauseValueDoesNotMatchSchema($e->name(), $e->value(), $addr, $e);
        }
    }

    /**
     * @return mixed[] like [offset => 10]
     */
    private function parseQueryArguments(RequestInterface $message): array
    {
        if ($message instanceof ServerRequestInterface) {
            return $message->getQueryParams();
        }

        return $this->parseQueryString($message->getUri()->getQuery());
    }

    /**
     * @see https://www.php.net/manual/en/function.parse-str.php#76792
     *
     * @return array
     */
    private function parseQueryString(string $queryString): array
    {
        $queryParameterPairs    = explode('&', urldecode($queryString));
        $filteredParameterPairs = array_filter(
            $queryParameterPairs,
            static function ($item) {
                return $item !== '';
            }
        );

        $arr = [];
        foreach ($filteredParameterPairs as $i) {
            [$key, $value] = explode('=', $i);

            if (! isset($arr[$key])) {
                $arr[$key] = $value;
                continue;
            }

            $arr[$key] = [$arr[$key], $value];
        }

        return $arr;
    }
}
