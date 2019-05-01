This project should be able to validate PSR7 requests/responses against an openapi specification.

1. Read openapi spec into the memory
2. Apply it on top of a given request/response objects

Naive validation for a response:
- check status code and content-type
- check if there are headers defined
- check if there are schema defined (The Schema Object allows the definition of input and output data types)

Refs:
- https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md
- https://github.com/cebe/php-openapi (good one, related to Yii community)
- https://github.com/Rebilly/openapi-php


