<?php

declare(strict_types=1);

namespace League\OpenAPIValidation\Tests\FromCommunity;

use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ServerRequestValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use PHPUnit\Framework\TestCase;

use function json_encode;

/**
 * @see https://github.com/lezhnev74/openapi-psr7-validator/issues/19
 */
final class Issue19Test extends TestCase
{
    /** @var string $yamlFile */
    private $yamlFile = __DIR__ . '/../stubs/date-times.yaml';
    /** @var ServerRequestValidator $validator */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = (new ValidatorBuilder())->fromYamlFile($this->yamlFile)->getServerRequestValidator();
    }

    public function testInvalidDateTime(): void
    {
        // For regression testing, try a date-time without a time zone (ie. an invalid value)
        $this->expectException(ValidationFailed::class);
        $this->validator->validate($this->makeRequest('2019-10-11T08:03:43'));
    }

    public function testDateTime(): void
    {
        // For regression testing, try the currently allowed date time format
        $this->validator->validate($this->makeRequest('2019-10-11T08:03:43Z'));
        $this->addToAssertionCount(1);
    }

    public function testDateTimeWithMilliseconds(): void
    {
        $this->validator->validate($this->makeRequest('2019-10-11T08:03:43.500Z'));
        $this->addToAssertionCount(1);
    }

    protected function makeRequest(string $dateTimeString): ServerRequest
    {
        $data['createdAt'] = $dateTimeString;
        $body              = json_encode($data);

        return new ServerRequest(
            'POST',
            'http://localhost:8000/products.create',
            ['Content-Type' => 'application/json'],
            $body
        );
    }
}
