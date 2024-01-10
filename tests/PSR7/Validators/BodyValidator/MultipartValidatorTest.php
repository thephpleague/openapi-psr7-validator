<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\PSR7\Validators\BodyValidator;

use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\UploadedFile;
use GuzzleHttp\Psr7\Uri;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidBody;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidHeaders;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

use function filesize;

class MultipartValidatorTest extends TestCase
{
    /**
     * @return string[][] of arguments
     */
    public function dataProviderMultipartGreen(): array
    {
        return [
            // Normal multipart message
            [
                <<<HTTP
POST /multipart HTTP/1.1
Content-Length: 428
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryOmz20xyMCkE27rN7

------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="id"
Content-Type: text/plain

123e4567-e89b-12d3-a456-426655440000
------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="token"

some-token
------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="address"
Content-Type: application/json

{
  "street": "3, Garden St",
  "city": "Hillsbery, UT"
}
------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="profileImage "; filename="image1.png"
Content-Type: application/octet-steam

{...file content...}
------WebKitFormBoundaryOmz20xyMCkE27rN7--
HTTP
,
            ],
            // multiple files with the same part name (array of files)
            [
                <<<HTTP
POST /multipart/files HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="fileName"; filename="file1.txt"
Content-Type: text/plain

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="fileName"; filename="file2.png"
Content-Type: image/png

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="fileName"; filename="file3.jpg"
Content-Type: image/jpeg

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
            ],
            // specified encoding for one part
            [
                <<<HTTP
POST /multipart/encoding HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="image"; filename="file1.txt"
Content-Type: specific/type

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
            ],
            // specified headers for one part
            [
                <<<HTTP
POST /multipart/encoding HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="image"; filename="file1.txt"
Content-Type: specific/type
X-Custom-Header: string value goes here
X-Numeric-Header: 1

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
            ],
            // specified headers for one part (wildcard)
            [
                <<<HTTP
POST /multipart/encoding/wildcard HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="image"; filename="file1.txt"
Content-Type: image/whatever

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
            ],
            // specified headers for one part (multiple, with charset)
            [
                <<<HTTP
POST /multipart/encoding/multiple HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="data"; filename="file1.txt"
Content-Type: APPLICATION/XML; CHARSET=UTF-8; OTHER=value

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
            ],
            // specified headers for one part (multiple, other valid type)
            [
                <<<HTTP
POST /multipart/encoding/multiple HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="data"; filename="file1.txt"
Content-Type: application/json ; charset="ISO-8859-1"

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
            ],
            // specified headers for one part (multiple, wildcard)
            [
                <<<HTTP
POST /multipart/encoding/multiple HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="data"; filename="file1.txt"
Content-Type: text/plain; charset=us-ascii

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
            ],
            // deserialized values
            [
                <<<HTTP
POST /multipart-deserialization HTTP/1.1
Content-Length: 428
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryOmz20xyMCkE27rN7

------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="id"
Content-Type: text/plain

123.0
------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="secure"
Content-Type: text/plain

true
------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="code"
Content-Type: text/plain

456
------WebKitFormBoundaryOmz20xyMCkE27rN7--
HTTP
,
            ],
        ];
    }

    /**
     * @return string[][] of arguments
     */
    public function dataProviderMultipartRed(): array
    {
        return [
            // wrong data in one of the parts
            [
                <<<HTTP
POST /multipart HTTP/1.1
Content-Length: 428
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryOmz20xyMCkE27rN7

------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="id"
Content-Type: text/plain

wrong uuid
------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="address"
Content-Type: application/json

{
  "street": "3, Garden St",
  "city": "Hillsbery, UT"
}
------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="profileImage "; filename="image1.png"
Content-Type: application/octet-steam

{...file content...}
------WebKitFormBoundaryOmz20xyMCkE27rN7--
HTTP
,
                InvalidBody::class,
            ],
            // wrong encoding for one of the part
            [
                <<<HTTP
POST /multipart/encoding HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="image"; filename="file1.txt"
Content-Type: invalid/type

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
                InvalidBody::class,
            ],
            // missing required part
            [
                <<<HTTP
POST /multipart/encoding HTTP/1.1
Content-Length: 428
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryOmz20xyMCkE27rN7

------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="description"
Content-Type: text/plain

123
------WebKitFormBoundaryOmz20xyMCkE27rN7--
HTTP
,
                InvalidBody::class,
            ],
            // wrong encoding charset for one of the parts (multiple)
            [
                <<<HTTP
POST /multipart/encoding/multiple HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="data"; filename="file1.txt"
Content-Type: application/xml; other=utf-8; charset=ISO-8859-1

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
                InvalidBody::class,
            ],
            // missing encoding charset for one of the parts (multiple)
            [
                <<<HTTP
POST /multipart/encoding/multiple HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="data"; filename="file1.txt"
Content-Type: application/xml

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
                InvalidBody::class,
            ],
            // wrong header for one part
            [
                <<<HTTP
POST /multipart/headers HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="image"; filename="file1.txt"
Content-Type: specific/type
X-Custom-Header-WRONG: string value goes here

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
                InvalidHeaders::class,
            ],
            // wrong header format for one part
            [
                <<<HTTP
POST /multipart/headers HTTP/1.1
Content-Length: 2740
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryWfPNVh4wuWBlyEyQ

------WebKitFormBoundaryWfPNVh4wuWBlyEyQ
Content-Disposition: form-data; name="image"; filename="file1.txt"
Content-Type: specific/type
X-Custom-Header: string value goes here
X-Numeric-Header: string value

[file content goes there]
------WebKitFormBoundaryWfPNVh4wuWBlyEyQ--
HTTP
,
                InvalidHeaders::class,
            ],
            // wrong data in one of the parts
            [
                <<<HTTP
POST /multipart-deserialization HTTP/1.1
Content-Length: 428
Content-Type: multipart/form-data; boundary=----WebKitFormBoundaryOmz20xyMCkE27rN7

------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="id"
Content-Type: text/plain

123
------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="secure"
Content-Type: text/plain

2
------WebKitFormBoundaryOmz20xyMCkE27rN7
Content-Disposition: form-data; name="code"
Content-Type: text/plain

456
------WebKitFormBoundaryOmz20xyMCkE27rN7--
HTTP
,
                InvalidBody::class,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderMultipartGreen
     */
    public function testValidateMultipartGreen(string $message): void
    {
        $specFile = __DIR__ . '/../../../stubs/multipart.yaml';

        $request       = Message::parseRequest($message); // convert a text HTTP message to a PSR7 message
        $serverRequest = new ServerRequest(
            $request->getMethod(),
            $request->getUri(),
            $request->getHeaders(),
            $request->getBody()
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($serverRequest);
        $this->addToAssertionCount(1);
    }

    /**
     * @dataProvider dataProviderMultipartRed
     */
    public function testValidateMultipartRed(string $message, string $expectedExceptionClass): void
    {
        $this->expectException($expectedExceptionClass);

        $specFile = __DIR__ . '/../../../stubs/multipart.yaml';

        $request       = Message::parseRequest($message); // convert a text HTTP message to a PSR7 message
        $serverRequest = new ServerRequest(
            $request->getMethod(),
            $request->getUri(),
            $request->getHeaders(),
            $request->getBody()
        );

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($serverRequest);
    }

    /**
     * @return mixed[][]
     */
    public function dataProviderMultipartServerRequestGreen(): array
    {
        $imagePath = __DIR__ . '/../../../stubs/image.jpg';
        $imageSize = filesize($imagePath);

        return [
            // Normal multipart message
            [
                'post',
                '/multipart',
                [
                    'id'      => 'bc8e1430-a963-11e9-a2a3-2a2ae2dbcce4',
                    'address' => [
                        'street' => 'Some street',
                        'city'   => 'some city',
                    ],
                ],
                [
                    'profileImage' => new UploadedFile($imagePath, $imageSize, 0),
                ],
            ],
            // Missing optional field with defined encoding
            [
                'post',
                '/multipart/encoding',
                [],
                [
                    'image' => new UploadedFile($imagePath, $imageSize, 0),
                ],
            ],
        ];
    }

    /**
     * @param string[]                             $body
     * @param array<string, UploadedFileInterface> $files
     *
     * @dataProvider dataProviderMultipartServerRequestGreen
     */
    public function testValidateMultipartServerRequestGreen(string $method, string $uri, array $body = [], array $files = []): void
    {
        $specFile = __DIR__ . '/../../../stubs/multipart.yaml';

        $serverRequest = (new ServerRequest($method, new Uri($uri)))
            ->withHeader('Content-Type', 'multipart/form-data')
            ->withParsedBody($body)
            ->withUploadedFiles($files);

        $validator = (new ValidatorBuilder())->fromYamlFile($specFile)->getServerRequestValidator();
        $validator->validate($serverRequest);
        $this->addToAssertionCount(1);
    }
}
