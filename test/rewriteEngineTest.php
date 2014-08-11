<?php

use Testify\Testify;

require '_routerBootstrap.php';

define("URL", "http://localhost/Via/test/");

require __DIR__ . '/../vendor/autoload.php';

$tf = new Testify("Via special tests");

$tf->test("Rewrite engine", function($tf) {
	$url = URL . "rewriteEngine.php";
	$urlClean = substr(URL, 0, -1);

	$req = Requests::get($url . '/test/');
	$tf->assertSame($req->body, '/test/*', '/test/');
	$req = Requests::get($urlClean . '/test/');
	$tf->assertSame($req->body, '/test/*', '/test/ [rewrite]');

	$req = Requests::get($url . '/test/asd');
	$tf->assertSame($req->body, '/test/*', '/test/asd');
	$req = Requests::get($urlClean . '/test/asd');
	$tf->assertSame($req->body, '/test/*', '/test/asd [rewrite]');

	$req = Requests::get($url . '//test');
	$tf->assertSame($req->body, '/*/test', '//test');
	$req = Requests::get($urlClean . '//test');
	$tf->assertSame($req->body, '/*/test', '//test [rewrite]');
});

$tf();
