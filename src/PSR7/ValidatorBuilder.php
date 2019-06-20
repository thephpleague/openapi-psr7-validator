<?php

declare(strict_types=1);

namespace OpenAPIValidation\PSR7;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Server;
use InvalidArgumentException;
use OpenAPIValidation\PSR7\SchemaFactory\JsonFactory;
use OpenAPIValidation\PSR7\SchemaFactory\PrecreatedSchemaFactory;
use OpenAPIValidation\PSR7\SchemaFactory\YamlFactory;
use OpenAPIValidation\PSR7\SchemaFactory\YamlFileFactory;
use Psr\Cache\CacheItemPoolInterface;
use const FILTER_VALIDATE_URL;
use function filter_var;
use function rtrim;
use function sprintf;
use function substr;
use function trim;

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
     * Used to prefix relative server urls
     *
     * @see https://swagger.io/docs/specification/api-host-and-base-path/#relative-urls
     *
     * @var string (i.e. https://localhost)
     */
    protected $serverUri;

    /**
     * @return $this
     */
    public function setCache(CacheItemPoolInterface $cache, ?int $ttl = null) : self
    {
        $this->cache = $cache;
        $this->ttl   = $ttl;

        return $this;
    }

    /**
     * @return $this
     */
    public function overrideCacheKey(string $cacheKey) : self
    {
        $this->cacheKey = $cacheKey;

        return $this;
    }

    public function setServerUri(string $uri) : self
    {
        $uri = trim($uri);
        $uri = rtrim($uri, '/');

        if (! filter_var($uri, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(sprintf("'%s' is invalid url", $uri));
        }

        $this->serverUri = $uri;

        return $this;
    }

    /**
     * @return $this
     */
    public function fromYaml(string $yaml) : self
    {
        $this->setSchemaFactory(new YamlFactory($yaml));

        return $this;
    }

    /**
     * @return $this
     */
    public function setSchemaFactory(SchemaFactory $schemaFactory) : self
    {
        $this->factory = $schemaFactory;

        return $this;
    }

    /**
     * @return $this
     */
    public function fromYamlFile(string $yamlFile) : self
    {
        $this->setSchemaFactory(new YamlFileFactory($yamlFile));

        return $this;
    }

    /**
     * @return $this
     */
    public function fromJson(string $json) : self
    {
        $this->setSchemaFactory(new JsonFactory($json));

        return $this;
    }

    /**
     * @return $this
     */
    public function fromJsonFile(string $jsonFile) : self
    {
        $this->setSchemaFactory(new JsonFactory($jsonFile));

        return $this;
    }

    /**
     * @return $this
     */
    public function fromSchema(OpenApi $schema) : self
    {
        $this->setSchemaFactory(new PrecreatedSchemaFactory($schema));

        return $this;
    }

    public function getServiceRequestValidator() : ServerRequestValidator
    {
        $schema = $this->getOrCreateSchema();

        if ($this->serverUri) {
            // prefix relative server urls with a given ServerUrl
            // @see https://github.com/lezhnev74/openapi-psr7-validator/issues/32
            $servers = [];
            foreach ($schema->servers as $server) {
                if (substr($server->url, 0, 1) === '/') {
                    // note: what about absolute linux paths?
                    $server = new Server([
                        'url'         => $this->serverUri . $server->url,
                        'description' => $server->description,
                        'variables'   => $server->variables,
                    ]);
                }

                $servers[] = $server;
            }
            $schema->servers = $servers;
        }

        return new ServerRequestValidator($schema);
    }

    protected function getOrCreateSchema() : OpenApi
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

    protected function getCacheKey() : string
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

    public function getResponseValidator() : ResponseValidator
    {
        return new ResponseValidator($this->getOrCreateSchema());
    }
}
