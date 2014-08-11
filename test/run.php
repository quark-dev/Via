<?php
/*
 * Run all tests
 */

use Testify\Testify;

require '_routerBootstrap.php';

define("URL", "http://localhost/Via/test/");

require __DIR__ . '/../vendor/autoload.php';

$tf = new Testify("Via tests");

include 'routesTest.php';
include 'methodsTest.php';
include 'requestsTest.php';
include 'responseTest.php';
include 'appTest.php';

$tf();
