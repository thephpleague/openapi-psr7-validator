<?php
/**
 * @author Dmitry Lezhnev <lezhnev.work@gmail.com>
 * Date: 01 May 2019
 */
declare(strict_types=1);

require __DIR__."/vendor/autoload.php";

$openapi = \cebe\openapi\Reader::readFromYamlFile(__DIR__.'/spec.yaml');

var_dump($openapi->paths['/observed/daily']->getOperations()['get']->parameters[0]);
