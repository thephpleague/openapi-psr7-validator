---
layout: default
permalink: /quick-start
title: Introduction
---

# Quick start

This library allows you use any pre-created YAML or JSON spec file as a source of the schema. 
Internally it utilizes [cebe/php-openapi](https://github.com/cebe/php-openapi) in order to
create proper schema objects from them, but also you can [create your own schema](/create-own-schema) from scratch  

For existing files using schema example is as easy as

```php
$yamlFile = "path/to/api.yaml";

$validator = (new \OpenAPIValidation\PSR7\ValidatorBuilder)->fromYamlFile($yamlFile)->getServerRequestValidator();

// Match is OperationAddress instanse relevant for matched spec operation
try {
    $match = $validator->validate($request);
} catch (ValidationFailed $e) {
    // inspect exception in order to get a cause
}
```

Builder has also other useful factory setup methods such as
* `fromYaml`
* `fromJsonFile`
* `fromJson`
* `fromJson`
* `fromSchema`

You can use builder to create validator for these supported types:
* [`Psr\Http\Message\RequestInterface`](/validators/request)
* [`Psr\Http\Message\ServerRequestInterface`](/validators/server_request)
* [`Psr\Http\Message\ResponseInterface`](/validators/response)

For `ServerRequestInterface` there is also a modified [`RoutedServerRequestValidator`](/validators/routed_server_request) 
which accepts `OperationAddress` alongside with object to validate. This allows you to
utilize external routing system instead of matching the request against all the schemas 
you have for you service, which should be significant faster
