<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function json_encode;

use const PHP_INT_MAX;

// This is another test which tests all request and response with variety of specified parameters:
// - path,
// - query arguments,
// - cookie,
// - request header,
// - request body,
// - response header
// - response body
final class CompleteTest extends TestCase
{
    /** @var string string */
    protected $apiSpecFile = __DIR__ . '/../stubs/complete.yaml';

    public function testRequestGreen(): void
    {
        $request = $this->buildGoodRequest();

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getServerRequestValidator();
        $validator->validate($request);
        $this->addToAssertionCount(1);
    }

    public function testResponseGreen(): void
    {
        $response = $this->buildGoodResponse();
        $addr     = new OperationAddress('/complete/{param1}/{param2}', 'post');

        $validator = (new ValidatorBuilder())->fromYamlFile($this->apiSpecFile)->getResponseValidator();
        $validator->validate($addr, $response);
        $this->addToAssertionCount(1);
    }

    protected function buildGoodRequest(): ServerRequestInterface
    {
        return (new ServerRequest('post', '/complete/good/2'))
            ->withQueryParams(['limit' => 10, 'filtering' => 'good'])
            ->withCookieParams(['session_id' => 100])
            ->withHeader('X-RequestId', 'abcd')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Utils::streamFor(json_encode(['propB' => 'good value'])));
    }

    protected function buildGoodResponse(): ResponseInterface
    {
        return (new Response())
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Utils::streamFor(json_encode(['propA' => PHP_INT_MAX])));
    }
}
