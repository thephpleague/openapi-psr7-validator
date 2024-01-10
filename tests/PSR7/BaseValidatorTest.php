<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use HansOtt\PSR7Cookies\SetCookie;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function json_encode;
use function sprintf;

abstract class BaseValidatorTest extends TestCase
{
    /** @var string */
    protected $apiSpecFile = __DIR__ . '/../stubs/api.yaml';

    protected function makeGoodResponse(string $path, string $method): ResponseInterface
    {
        switch ($method . ' ' . $path) {
            case 'get /path1':
                $body = ['propA' => 1, 'propD' => [1, 'string', null]];

                return (new Response())
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Header-B', 'good value')
                    ->withBody(Utils::streamFor(json_encode($body)));

            case 'post /cookies':
                $response = (new Response())
                    ->withHeader('Content-Type', 'text/plain');
                $response = SetCookie::thatStaysForever('session_id', 'abc')->addToResponse($response);

                return $response;

            default:
                throw new InvalidArgumentException(sprintf("unexpected operation '%s %s''", $method, $path));
        }
    }

    protected function makeGoodServerRequest(string $path, string $method): ServerRequestInterface
    {
        $request = new ServerRequest($method, $path);

        switch ($method . ' ' . $path) {
            case 'get /read':
                return $request
                    ->withUri(new Uri($path . '?filter=age&limit=10'))
                    ->withQueryParams(['filter' => 'age', 'limit' => 10, 'offset' => 0]);

            case 'get /path1':
                return $request
                    ->withUri(new Uri($path . '?queryArgA=20'))
                    ->withHeader('Header-A', 'value A');

            case 'post /cookies':
                return $request
                    ->withCookieParams(['session_id' => 'abc', 'debug' => 10])
                    ->withHeader('Content-Type', 'text/plain');

            case 'post /request-body':
                $body = ['name' => 'Alex'];

                return $request
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody(Utils::streamFor(json_encode($body)));

            default:
                throw new InvalidArgumentException(sprintf("unexpected operation '%s %s''", $method, $path));
        }
    }

    protected function makeGoodRequest(string $path, string $method): RequestInterface
    {
        $request = new Request($method, $path);

        switch ($method . ' ' . $path) {
            case 'get /read':
                return $request
                    ->withUri(new Uri($path . '?filter=age&limit=10&offset=0'));

            case 'get /path1':
                return $request
                    ->withUri(new Uri($path . '?queryArgA=20'))
                    ->withHeader('Header-A', 'value A');

            case 'post /cookies':
                $request = $request->withHeader('Content-Type', 'text/plain');
                $request = $request->withHeader('Cookie', 'session_id=abc; debug=10');

                return $request;

            case 'post /request-body':
                $body = ['name' => 'Alex'];

                return $request
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody(Utils::streamFor(json_encode($body)));

            default:
                throw new InvalidArgumentException(sprintf("unexpected operation '%s %s''", $method, $path));
        }
    }
}
