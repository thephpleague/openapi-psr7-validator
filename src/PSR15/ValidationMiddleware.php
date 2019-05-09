<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 03 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidation\PSR15;


use OpenAPIValidation\PSR7\ResponseValidator;
use OpenAPIValidation\PSR7\ServerRequestValidator;
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

    /**
     * @param string $oasType
     * @param string $oasContent
     */
    protected function __construct(string $oasType, string $oasContent)
    {
        Validator::in(['json', 'yaml', 'jsonFile', 'yamlFile'])->assert($oasType);

        $this->oasType    = $oasType;
        $this->oasContent = $oasContent;
    }

    static function fromYaml(string $yaml): self
    {
        return new static('yaml', $yaml);
    }

    static function fromJson(string $json): self
    {
        return new static('json', $json);
    }

    static function fromYamlFile(string $yamlFile): self
    {
        Validator::file()->assert($yamlFile);

        return new static('yamlFile', $yamlFile);
    }

    static function fromJsonFile(string $jsonFile): self
    {
        Validator::file()->assert($jsonFile);

        return new static('jsonFile', $jsonFile);
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
        switch ($this->oasType) {
            case "json":
                $serverRequestValidator = ServerRequestValidator::fromJson($this->oasContent);
                $responseValidator      = ResponseValidator::fromJson($this->oasContent);
                break;
            case "jsonFile":
                $serverRequestValidator = ServerRequestValidator::fromJsonFile($this->oasContent);
                $responseValidator      = ResponseValidator::fromJsonFile($this->oasContent);
                break;
            case "yaml":
                $serverRequestValidator = ServerRequestValidator::fromYaml($this->oasContent);
                $responseValidator      = ResponseValidator::fromYaml($this->oasContent);
                break;
            case "yamlFile":
                $serverRequestValidator = ServerRequestValidator::fromYamlFile($this->oasContent);
                $responseValidator      = ResponseValidator::fromYamlFile($this->oasContent);
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