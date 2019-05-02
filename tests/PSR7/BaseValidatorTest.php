<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 02 May 2019
 */
declare(strict_types=1);


namespace OpenAPIValidationTests\PSR7;


use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function GuzzleHttp\Psr7\stream_for;

abstract class BaseValidatorTest extends TestCase
{
    protected $apiSpecFile = __DIR__ . "/../openapi_stubs/api.yaml";

    protected function makeGoodResponse(string $path, string $method): ResponseInterface
    {
        switch ("$method $path") {
            case "get /path1":
                $body = ['propA' => 1];
                return (new Response())
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Header-B', 'good value')
                    ->withBody(stream_for(json_encode($body)));
            default:
                throw new \Exception("unexpected operation '$method $path''");
        }
    }

    protected function makeGoodServerRequest(string $path, string $method): ServerRequestInterface
    {
        $request = new ServerRequest($method, $path);

        switch ("$method $path") {
            case "get /path1":
                $body = ['propA' => 1];
                return $request
                    ->withUri(new Uri("$path?queryArgA=20"))
                    ->withHeader('Header-A', 'value A');
            default:
                throw new \Exception("unexpected operation '$method $path''");
        }
    }
}