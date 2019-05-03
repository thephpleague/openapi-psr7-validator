<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR15;


use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use OpenAPIValidation\PSR7\ResponseValidator;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationMiddleware implements MiddlewareInterface
{
    /** @var string - full path to the YAML file */
    private $OASSpecFile;
    /** @var string - json, yaml */
    private $fileFormat;

    /**
     * @param string $OASSpecFile
     * @param string $fileFormat
     */
    private function __construct(string $OASSpecFile, string $fileFormat)
    {
        if (!is_file($OASSpecFile)) {
            throw new \RuntimeException(sprintf("File '%s' not available", $OASSpecFile));
        }

        $this->OASSpecFile = $OASSpecFile;
        $this->fileFormat  = $fileFormat;
    }

    static function fromYamlSpec(string $specFile): self
    {
        return new self($specFile, 'yaml');
    }

    static function fromJsonSpec(string $specFile): self
    {
        return new self($specFile, 'json');
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $oas = $this->readOAS();

        // 1. Validate request
        $validator           = new ServerRequestValidator($oas);
        $matchedOASOperation = $validator->validate($request);

        // 2. Response
        $response  = $handler->handle($request);
        $validator = new ResponseValidator($oas);
        $validator->validate($matchedOASOperation, $response);

        return $response;
    }

    /**
     * Read OAS in a given format
     *
     * @return OpenApi
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     */
    protected function readOAS(): OpenApi
    {
        if ($this->fileFormat == 'yaml') {
            return Reader::readFromYamlFile($this->OASSpecFile);
        } else {
            return Reader::readFromJsonFile($this->OASSpecFile);
        }
    }

}