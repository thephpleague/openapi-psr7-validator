# OpenAPI PSR-7 Message (HTTP Request/Response) Validator

This package can validate PSR-7 messages against OpenAPI (3.0.2) specifications 
expressed in YAML or JSON. 

![](image.jpg)

## Requirements
n/a

## Installation
```
composer require lezhnev74/openapi-validator
```

## How To
### ServerRequest Message
add docs
### Response Message
add docs
### Request Message
`\Psr\Http\Message\RequestInterface` validation is not implemented. 

## Standalone OpenAPI Validator
The package contains a standalone validator which can validate any data 
against an OpenAPI schema like this:
```php
$spec = <<<SPEC
schema:
  type: string
  enum:
  - a
  - b
SPEC;
$data = "a";

$spec   = cebe\openapi\Reader::readFromYaml($spec);
$schema = new cebe\openapi\spec\Schema($spec->schema);
(new OpenAPIValidation\Schema\Validator($schema, $data))->validate();
```

## Custom Type Formats
As you know, OAS allows you to add formats to types:
```yaml
schema:
  type: string
  format: binary
```
This package contains a bunch of built-in format validators:
- `string` type:
    - `byte`
    - `date`
    - `date-time`
    - `email`
    - `hostname`
    - `ipv4`
    - `ipv6`
    - `uri`
    - `uuid` (uuid4)
- `number` type
    - `float`
    - `double`

You can also add your own formats. Like this:
```php
# A format validator can be a callable
# failed validation should throw an exception
$customFormat = new class()
{
    function __invoke($value): void
    {
        if ($value != "good value") {
            throw FormatMismatch::fromFormat('custom', $value);
        }
    }
};

# Register your callable like this before validating tyour data
\OpenAPIValidation\Schema\TypeFormats\FormatsContainer::registerFormat('string', 'unexpected', $unexpectedFormat);
```

## Testing
You can run the tests with:

```
vendor/bin/phpunit
```

## Beta version
**Still in BETA** (but useful already).
If you foudn something does not work as expected (occasionally this happens),
 I'd appreciate it if you open a new issue and attach OpenAPI spec which 
 caused the problem. That would simplify and speed up the fixing process.

## Credits
- [Dmitry Lezhnev](https://github.com/lezhnev74)
- Icons made by Freepik, licensed by CC 3.0 BY
- [slim3-psr15](https://github.com/bnf/slim3-psr15) package for Slim 
middleware adapter

A big thank you to [Henrik Karlström](https://github.com/hkarlstrom) who kind
 of inspired me to work on this package.
 
## License
The MIT License (MIT). Please see `License.md` file for more information.

## TODO
- [ ] parameters serialization
    - Does anyone use this serialization? It looks very... unpractical.
- [ ] add validation for Request class.
    - Usually for serverside testing purposes ServerRequest is what we need. 
    But, Request should be quite easy to add.