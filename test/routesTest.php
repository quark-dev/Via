<?php

$tf->test("Basic routes", function($tf) {
	$url = URL . "routes.php";

	$req = Requests::get($url);
	$tf->assertSame($req->body, "''all", '[nothing]');

	$req = Requests::get($url . '/');
	$tf->assertSame($req->body, "''/", '/');

	$req = Requests::get($url . '//');
	$tf->assertSame($req->body, '/', '//');

	$req = Requests::get($url . '/test/');
	$tf->assertSame($req->body, '/test/*', '/test/');

	$req = Requests::get($url . '/test/asd');
	$tf->assertSame($req->body, '/test/*', '/test/asd');

	$req = Requests::get($url . '//test');
	$tf->assertSame($req->body, '/*/test', '//test');

	$req = Requests::get($url . '/asd/test');
	$tf->assertSame($req->body, '/*/test', '/asd/test');

	$req = Requests::get($url . '/space/');
	$tf->assertSame($req->body, '/space', '/space/');

	$req = Requests::get($url . '/space//');
	$tf->assertSame($req->body, '/space/', '/space//');

	$req = Requests::get($url . '/space/lala/foo');
	$tf->assertSame($req->body, '/space/*', '/space/lala/foo');

	$req = Requests::get($url . '/space/42');
	$tf->assertSame($req->body, '/space/42', '/space/42');

	$req = Requests::get($url . '/fn');
	$tf->assertSame($req->body, '/fn', '/fn (external function)');

	$req = Requests::get($url . '/123/312/123');
	$tf->assertSame($req->body, 'all');
});


$tf->test("Advanced routes (regex)", function($tf) {
	$url = URL . "routes.php";

	$req = Requests::get($url . '/r/ReGeX');
	$tf->assertSame($req->body, "regex [a-zA-Z]+");

	$req = Requests::get($url . '/r/_');
	$tf->assertSame($req->body, "regex ([a-zA-Z_-]+) _");

	$req = Requests::get($url . '/r/ReGeX_');
	$tf->assertSame($req->body, "regex ([a-zA-Z_-]+) ReGeX_");

	$req = Requests::get($url . '/r/ReGeX');
	$tf->assertSame($req->body, "regex [a-zA-Z]+");

	$req = Requests::get($url . '/r/0');
	$tf->assertSame($req->body, "regex .+");
});


$tf->test("Advanced routes (controller)", function($tf) {
	$url = URL . "routes.php";

	$req = Requests::get($url . '/c/0');
	$tf->assertSame($req->body, "method", "Call method");

	$req = Requests::get($url . '/c/1');
	$tf->assertSame($req->body, "methodNextmethod2", "Call method and test \$next()");

	$req = Requests::get($url . '/c/2/Method');
	$tf->assertSame($req->body, "GET test", "Call getMethod()");

	$req = Requests::get($url . '/c/2/Method2');
	$tf->assertSame($req->body, "GET test2", "Call gEtMeThOd2()");

	$req = Requests::post($url . '/c/2/Method');
	$tf->assertNotSame($req->body, "POST test", "Cannot call postMethod()");

	$req = Requests::get($url . '/c/3/Method/sufix');
	$tf->assertSame($req->body, "GET test", "Call getMethod() 2");

	$req = Requests::get($url . '/c/3/Method/abc');
	$tf->assertNotSame($req->body, "GET test", "Not getMethod() 2");

	$req = Requests::get($url . '/c/4/Method');
	$tf->assertSame($req->body, "GET test", "Call getMethod()");

	$req = Requests::post($url . '/c/4/Method');
	$tf->assertSame($req->body, "POST test", "Call postMethod()");

	$req = Requests::put($url . '/c/4/Method');
	$tf->assertSame($req->body, "PUT test", "Call putMethod()");
});

