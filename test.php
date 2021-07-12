<?php

use cebe\openapi\Reader;
use GuzzleHttp\Psr7\ServerRequest;
use League\OpenAPIValidation\PSR7\WebHookServerRequestValidator;

require 'vendor/autoload.php';

$openApi = Reader::readFromYaml(file_get_contents('github.yaml'));
$whsrv = new WebHookServerRequestValidator($openApi);

$whsrv->validate(new ServerRequest('POST', '/webhook', ['X-GitHub-Event' => 'installation']));
