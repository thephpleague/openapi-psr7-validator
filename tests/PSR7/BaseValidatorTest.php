<?php

declare(strict_types=1);

namespace OpenAPIValidationTests\PSR7;

use Exception;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function GuzzleHttp\Psr7\stream_for;
use function json_encode;

abstract class BaseValidatorTest extends TestCase
{
    protected $apiSpecFile = __DIR__ . '/../stubs/api.yaml';

    protected function makeGoodResponse(string $path, string $method) : ResponseInterface
    {
        switch ("$method $path") {
            case 'get /path1':
                $body = ['propA' => 1];

                return (new Response())
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Header-B', 'good value')
                    ->withBody(stream_for(json_encode($body)));
            case 'post /cookies':
                return (new Response())
                    ->withHeader('Content-Type', 'text/plain')
                    ->withHeader('Set-Cookie', 'anyName=anyValue');
            default:
                throw new Exception("unexpected operation '$method $path''");
        }
    }

    protected function makeGoodServerRequest(string $path, string $method) : ServerRequestInterface
    {
        $request = new ServerRequest($method, $path);

        switch ("$method $path") {
            case 'get /read':
                return $request
                    ->withUri(new Uri("$path?filter=age&limit=10"))
                    ->withQueryParams(['filter' => 'age', 'limit' => 10, 'offset' => 0]);
            case 'get /path1':
                return $request
                    ->withUri(new Uri("$path?queryArgA=20"))
                    ->withHeader('Header-A', 'value A');
            case 'post /cookies':
                return $request
                    ->withCookieParams(['session_id' => 'abc', 'debug' => 10])
                    ->withHeader('Content-Type', 'text/plain');
            case 'post /request-body':
                $body = ['name' => 'Alex'];

                return $request
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody(stream_for(json_encode($body)));
            default:
                throw new Exception("unexpected operation '$method $path''");
        }
    }
}
