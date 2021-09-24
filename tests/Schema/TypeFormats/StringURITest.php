<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\Schema\TypeFormats;

use League\OpenAPIValidation\Schema\TypeFormats\StringURI;
use PHPUnit\Framework\TestCase;

use function rawurlencode;

class StringURITest extends TestCase
{
    /**
     * @return array<array<string>>
     */
    public function greenURIDataProvider(): array
    {
        return [
            'about:blank' => ['about:blank'],
            'scheme with non-leading digit' => ['s3://somebucket/somefile.txt'],
            'uri with host ascii version' => ['scheme://user:pass@xn--mgbh0fb.xn--kgbechtv'],
            'complete URI' => ['scheme://user:pass@host:81/path?query#fragment'],
            'URI is not normalized' => ['ScheMe://user:pass@HoSt:81/path?query#fragment'],
            'URI without scheme' => ['//user:pass@HoSt:81/path?query#fragment'],
            'URI without empty authority only' => ['//'],
            'URI without userinfo' => ['scheme://HoSt:81/path?query#fragment'],
            'URI with empty userinfo' => ['scheme://@HoSt:81/path?query#fragment'],
            'URI without port' => ['scheme://user:pass@host/path?query#fragment'],
            'URI with an empty port' => ['scheme://user:pass@host:/path?query#fragment'],
            'URI without user info and port' => ['scheme://host/path?query#fragment'],
            'URI with host IP' => ['scheme://10.0.0.2/p?q#f'],
            'URI with scoped IP' => ['scheme://[fe80:1234::%251]/p?q#f'],
            'URI with IP future' => ['scheme://[vAF.1::2::3]/p?q#f'],
            'URI without authority' => ['scheme:path?query#fragment'],
            'URI without authority and scheme' => ['/path'],
            'URI with empty host' => ['scheme:///path?query#fragment'],
            'URI with empty host and without scheme' => ['///path?query#fragment'],
            'URI without path' => ['scheme://[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]?query#fragment'],
            'URI without path and scheme' => ['//[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]?query#fragment'],
            'URI without scheme with IPv6 host and port' => ['//[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:42?query#fragment'],
            'complete URI without scheme' => ['//user@[FEDC:BA98:7654:3210:FEDC:BA98:7654:3210]:42?q#f'],
            'URI without authority and query' => ['scheme:path#fragment'],
            'URI with empty query' => ['scheme:path?#fragment'],
            'URI with query only' => ['?query'],
            'URI without fragment' => ['tel:05000'],
            'URI with empty fragment' => ['scheme:path#'],
            'URI with fragment only' => ['#fragment'],
            'URI with empty fragment only' => ['#'],
            'URI without authority 2' => ['path#fragment'],
            'URI with empty query and fragment' => ['?#'],
            'URI with absolute path' => ['/?#'],
            'URI with absolute authority' => ['https://thephpleague.com./p?#f'],
            'URI with absolute path only' => ['/'],
            'URI with empty query only' => ['?'],
            'relative path' => ['../relative/path'],
            'complex authority' => ['http://a_.!~*\'(-)n0123Di%25%26:pass;:&=+$,word@www.zend.com'],
            'complex authority without scheme' => ['//a_.!~*\'(-)n0123Di%25%26:pass;:&=+$,word@www.zend.com'],
            'single word is a path' => ['http'],
            'URI scheme with an empty authority' => ['http://'],
            'single word is a path, no' => ['http:::/path'],
            'fragment with pseudo segment' => ['http://example.com#foo=1/bar=2'],
            'empty string' => [''],
            'complex URI' => ['htà+d/s:totot'],
            'scheme only URI' => ['http:'],
            'RFC3986 LDAP example' => ['ldap://[2001:db8::7]/c=GB?objectClass?one'],
            'RFC3987 example' => ['http://bébé.bé./有词法别名.zh'],
            'colon detection respect RFC3986 (1)' => ['http://example.org/hello:12?foo=bar#test'],
            'colon detection respect RFC3986 (2)' => ['/path/to/colon:34'],
            'scheme with hyphen' => ['android-app://org.wikipedia/http/en.m.wikipedia.org/wiki/The_Hitchhiker%27s_Guide_to_the_Galaxy'],
            'Authority is the colon' => ['ftp://:/p?q#f'],
            'URI with 0 leading port' => ['scheme://user:pass@host:000000000081/path?query#fragment'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function redURIDataProvider(): array
    {
        return [
            'invalid scheme' => ['0scheme://host/path?query#fragment'],
            'invalid path' => ['://host:80/p?q#f'],
            'invalid port (1)' => ['//host:port/path?query#fragment'],
            'invalid port (2)' => ['//host:-892358/path?query#fragment'],
            'invalid host' => ['http://exam ple.com'],
            'invalid ipv6 host (1)' => ['scheme://[127.0.0.1]/path?query#fragment'],
            'invalid ipv6 host (2)' => ['scheme://]::1[/path?query#fragment'],
            'invalid ipv6 host (3)' => ['scheme://[::1|/path?query#fragment'],
            'invalid ipv6 host (4)' => ['scheme://|::1]/path?query#fragment'],
            'invalid ipv6 host (5)' => ['scheme://[::1]./path?query#fragment'],
            'invalid ipv6 host (6)' => ['scheme://[[::1]]:80/path?query#fragment'],
            'invalid ipv6 scoped (1)' => ['scheme://[::1%25%23]/path?query#fragment'],
            'invalid ipv6 scoped (2)' => ['scheme://[fe80::1234::%251]/path?query#fragment'],
            'invalid char on URI' => ["scheme://host/path/\r\n/toto"],
            'invalid path only URI' => ['2620:0:1cfe:face:b00c::3'],
            'invalid path PHP bug #72811' => ['[::1]:80'],
            'invalid ipvfuture' => ['//[v6.::1]/p?q#f'],
            'invalid RFC3987 host' => ['//a⒈com/p?q#f'],
            'invalid RFC3987 host URL encoded' => ['//' . rawurlencode('a⒈com') . '/p?q#f'],
            'invalid Host with fullwith (1)' => ['http://％００.com'],
            'invalid host with fullwidth escaped' => ['http://%ef%bc%85%ef%bc%94%ef%bc%91.com],'],
            // 'invalid pseudo IDN to ASCII string' => ['http://xn--3/foo.'],
            'invalid IDN' => ['//:�@�����������������������������������������������������������������������������������������/'],
        ];
    }

    /**
     * @dataProvider greenURIDataProvider
     */
    public function testGreenURIFormat(string $uri): void
    {
        $this->assertTrue((new StringURI())($uri));
    }

    /**
     * @dataProvider redURIDataProvider
     */
    public function testRedURIFormat(string $uri): void
    {
        $this->assertFalse((new StringURI())($uri));
    }
}
