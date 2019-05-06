<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 06 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidationTests\PSR7;


use cebe\openapi\Reader;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use OpenAPIValidation\PSR7\OperationAddress;
use OpenAPIValidation\PSR7\ResponseValidator;
use OpenAPIValidation\PSR7\ServerRequestValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function GuzzleHttp\Psr7\stream_for;

// This is another test which tests all request and response with variety of specified parameters:
// - path,
// - query arguments,
// - cookie,
// - request header,
// - request body,
// - response header
// - response body
class CompleteTest extends TestCase
{
    protected $apiSpecFile = __DIR__ . "/../stubs/complete.yaml";

    protected function buildGoodRequest(): ServerRequestInterface
    {
        return (new ServerRequest("post", "/complete/good/2"))
            ->withQueryParams(['limit'=>10, 'filtering'=>'good'])
            ->withCookieParams(['session_id' => 100])
            ->withHeader('X-RequestId', 'abcd')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(stream_for(json_encode(['propB' => 'good value'])));
    }

    protected function buildGoodResponse(): ResponseInterface
    {
        return (new Response())
            ->withHeader('Content-Type', 'application/json')
            ->withBody(stream_for(json_encode(['propA' => PHP_INT_MAX])));
    }


    function test_request_green()
    {
        $request = $this->buildGoodRequest();

        $validator = new ServerRequestValidator(Reader::readFromYamlFile($this->apiSpecFile));
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    function test_response_green()
    {
        $response = $this->buildGoodResponse();
        $addr = new OperationAddress("/complete/{param1}/{param2}", "post");

        $validator = new ResponseValidator(Reader::readFromYamlFile($this->apiSpecFile));
        $validator->validate($addr, $response);
        $this->addToAssertionCount(1);
    }

}