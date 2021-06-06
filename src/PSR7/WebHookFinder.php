<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Server;

use function count;
use function ltrim;
use function parse_url;
use function preg_match;
use function preg_replace;
use function rtrim;
use function sprintf;
use function strtolower;

use const PHP_URL_PATH;

// This class finds operations matching the given URI+method
// That would be a very simple operation if there were no "Servers" keyword.
// We need to take into account possible base-url case (and its templating feature)
// @see https://swagger.io/docs/specification/api-host-and-base-path/
//
// More: as discussed here https://github.com/lezhnev74/openapi-psr7-validator/issues/32
// "schema://hostname" should not be included in the validation process (assume any hostname matches)
class WebHookFinder
{
    /** @var OpenApi */
    protected $openApiSpec;
    /** @var string */
    protected $event;
    /** @var string $method like "get" */
    protected $method;
    /** @var Operation[] */
    protected $searchResult;

    public function __construct(OpenApi $openApiSpec, string $event, string $method)
    {
        $this->openApiSpec = $openApiSpec;
        $this->event        = $event;
        $this->method      = strtolower($method);
    }

    /**
     * Determine matching paths.
     *
     * @return PathItem[]
     */
    public function getWebHookMatches(): array
    {
        // Determine if path matches exactly.
        $match = $this->openApiSpec->webhooks->getWebHook($this->event);
        if ($match !== null) {
            return [$match];
        }

        return [];
    }

    /**
     * @return Operation[]
     */
    public function search(): array
    {
        if ($this->searchResult === null) {
            $this->searchResult = $this->doSearch();
        }

        return $this->searchResult;
    }

    /**
     * @return Operation[]
     */
    private function doSearch(): array
    {
        $matchedOperations = [];

        if ($this->openApiSpec->webhooks->hasWebHook($this->event)) {
            foreach ($this->openApiSpec->webhooks->getWebHook($this->event)->getOperations() as $opMethod => $operation) {
                if ($opMethod !== $this->method) {
                    continue;
                }

                $matchedOperations[] = $operation;
            }
        }

        return $matchedOperations;
    }
}
