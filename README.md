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

## Testing
You can run the tests with:

```
vendor/bin/phpunit
```

## Credits
- [Dmitry Lezhnev](https://github.com/lezhnev74)
- Icons made by Freepik, licensed by CC 3.0 BY

A big thank you to [Henrik Karlström](https://github.com/hkarlstrom) who kind
 of inspired me to work on this package.
 
## License
The MIT License (MIT). Please see `License.md` file for more information.

## TODO
- [ ] add default type formats
- [ ] add ability to add custom type formats
- [ ] cookie parameters validation
- [ ] path parameters validation
- [ ] query parameters validation