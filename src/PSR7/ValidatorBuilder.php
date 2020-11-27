<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use InvalidArgumentException;
use League\OpenAPIValidation\PSR7\SchemaFactory\JsonFactory;
use League\OpenAPIValidation\PSR7\SchemaFactory\JsonFileFactory;
use League\OpenAPIValidation\PSR7\SchemaFactory\PrecreatedSchemaFactory;
use League\OpenAPIValidation\PSR7\SchemaFactory\YamlFactory;
use League\OpenAPIValidation\PSR7\SchemaFactory\YamlFileFactory;
use Psr\Cache\CacheItemPoolInterface;

class ValidatorBuilder
{
    /** @var SchemaFactory */
    protected $factory;
    /** @var CacheItemPoolInterface */
    protected $cache;
    /** @var int|null */
    protected $ttl;
    /** @var string */
    protected $cacheKey;

    /**
     * @return $this
     */
    public function setCache(CacheItemPoolInterface $cache, ?int $ttl = null): self
    {
        $this->cache = $cache;
        $this->ttl   = $ttl;

        return $this;
    }

    /**
     * @return $this
     */
    public function overrideCacheKey(string $cacheKey): self
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    /**
     * @return $this
     */
    public function fromYaml(string $yaml): self
    {
        $this->setSchemaFactory(new YamlFactory($yaml));

        return $this;
    }

    /**
     * @return $this
     */
    public function setSchemaFactory(SchemaFactory $schemaFactory): self
    {
        $this->factory = $schemaFactory;

        return $this;
    }

    /**
     * @return $this
     */
    public function fromYamlFile(string $yamlFile): self
    {
        $this->setSchemaFactory(new YamlFileFactory($yamlFile));

        return $this;
    }

    /**
     * @return $this
     */
    public function fromJson(string $json): self
    {
        $this->setSchemaFactory(new JsonFactory($json));

        return $this;
    }

    /**
     * @return $this
     */
    public function fromJsonFile(string $jsonFile): self
    {
        $this->setSchemaFactory(new JsonFileFactory($jsonFile));

        return $this;
    }

    /**
     * @return $this
     */
    public function fromSchema(OpenApi $schema): self
    {
        $this->setSchemaFactory(new PrecreatedSchemaFactory($schema));

        return $this;
    }

    public function getServerRequestValidator(): ServerRequestValidator
    {
        $schema = $this->getOrCreateSchema();

        return new ServerRequestValidator($schema);
    }

    public function getRequestValidator(): RequestValidator
    {
        $schema = $this->getOrCreateSchema();

        return new RequestValidator($schema);
    }

    public function getResponseValidator(): ResponseValidator
    {
        return new ResponseValidator($this->getOrCreateSchema());
    }

    public function getRoutedRequestValidator(): RoutedServerRequestValidator
    {
        return new RoutedServerRequestValidator($this->getOrCreateSchema());
    }

    public function getCallbackRequestValidator(): CallbackRequestValidator
    {
        return new CallbackRequestValidator($this->getOrCreateSchema());
    }

    public function getCallbackResponseValidator(): CallbackResponseValidator
    {
        return new CallbackResponseValidator($this->getOrCreateSchema());
    }

    protected function getOrCreateSchema(): OpenApi
    {
        // Make cache dependency optional for end user
        if ($this->cache === null) {
            return $this->factory->createSchema();
        }

        $cacheKey = $this->getCacheKey();

        $item = $this->cache->getItem($cacheKey);

        if ($item->isHit()) {
            return $item->get();
        }

        $schema = $this->factory->createSchema();

        $item->set($schema);
        $item->expiresAfter($this->ttl);

        $this->cache->save($item);

        return $schema;
    }

    protected function getCacheKey(): string
    {
        if ($this->cacheKey !== null) {
            return $this->cacheKey;
        }

        if (! $this->factory instanceof CacheableSchemaFactory) {
            throw new InvalidArgumentException(
                'Either provide cache key manually or use instance of ' . CacheableSchemaFactory::class
            );
        }

        return $this->factory->getCacheKey();
    }
}
