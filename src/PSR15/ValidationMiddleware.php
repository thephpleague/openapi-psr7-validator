<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR15;

use OpenAPIValidation\PSR7\ResponseValidator;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Respect\Validation\Validator;

class ValidationMiddleware implements MiddlewareInterface
{
    /** @var string in(yaml,yamlFile,json,jsonFile) */
    private $oasType;
    /** @var string */
    private $oasContent;
    /** @var CacheItemPoolInterface */
    private $cachePool;

    protected function __construct(string $oasType, string $oasContent, ?CacheItemPoolInterface $cache)
    {
        Validator::in(['json', 'yaml', 'jsonFile', 'yamlFile'])->assert($oasType);

        $this->oasType    = $oasType;
        $this->oasContent = $oasContent;
        $this->cachePool  = $cache;
    }

    public static function fromYaml(string $yaml, ?CacheItemPoolInterface $cache = null) : self
    {
        return new static('yaml', $yaml, $cache);
    }

    public static function fromJson(string $json, ?CacheItemPoolInterface $cache = null) : self
    {
        return new static('json', $json, $cache);
    }

    public static function fromYamlFile(string $yamlFile, ?CacheItemPoolInterface $cache = null) : self
    {
        Validator::file()->assert($yamlFile);

        return new static('yamlFile', $yamlFile, $cache);
    }

    public static function fromJsonFile(string $jsonFile, ?CacheItemPoolInterface $cache = null) : self
    {
        Validator::file()->assert($jsonFile);

        return new static('jsonFile', $jsonFile, $cache);
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        switch ($this->oasType) {
            case 'json':
                $serverRequestValidator = ServerRequestValidator::fromJson($this->oasContent, $this->cachePool);
                $responseValidator      = ResponseValidator::fromJson($this->oasContent, $this->cachePool);
                break;
            case 'jsonFile':
                $serverRequestValidator = ServerRequestValidator::fromJsonFile($this->oasContent, $this->cachePool);
                $responseValidator      = ResponseValidator::fromJsonFile($this->oasContent, $this->cachePool);
                break;
            case 'yaml':
                $serverRequestValidator = ServerRequestValidator::fromYaml($this->oasContent, $this->cachePool);
                $responseValidator      = ResponseValidator::fromYaml($this->oasContent, $this->cachePool);
                break;
            case 'yamlFile':
                $serverRequestValidator = ServerRequestValidator::fromYamlFile($this->oasContent, $this->cachePool);
                $responseValidator      = ResponseValidator::fromYamlFile($this->oasContent, $this->cachePool);
                break;
        }

        // 1. Validate request
        $matchedOASOperation = $serverRequestValidator->validate($request);

        // 2. Response
        $response = $handler->handle($request);
        $responseValidator->validate($matchedOASOperation, $response);

        return $response;
    }
}
