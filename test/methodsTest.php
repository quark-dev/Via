<?php

$tf->test("Methods test", function($tf) {
	$url = URL . "methods.php";

	$req = Requests::get($url . '/');
	$tf->assertSame($req->body, 'GET /');

	$req = Requests::post($url . '/');
	$tf->assertSame($req->body, 'POST /');

	$req = Requests::put($url . '/');
	$tf->assertSame($req->body, 'PUT /');

	$req = Requests::delete($url . '/');
	$tf->assertSame($req->body, 'DELETE /');

	$req = Requests::get($url . '/on');
	$tf->assertSame($req->body, 'GET /on');

	$req = Requests::put($url . '/on');
	$tf->assertSame($req->body, 'PUT /on');

	$req = Requests::get($url . '/multi');
	$tf->assertSame($req->body, 'MULTI /multi');

	$req = Requests::put($url . '/multi');
	$tf->assertSame($req->body, 'MULTI /multi');

	$req = Requests::post($url . '/multi');
	$tf->assertSame($req->body, 'MULTI /multi');

	$req = Requests::delete($url . '/multi');
	$tf->assertNotSame($req->body, 'MULTI /multi');

	$req = Requests::get($url . '/any');
	$tf->assertSame($req->body, 'ANY /any');

	$req = Requests::post($url . '/any');
	$tf->assertSame($req->body, 'ANY /any');

	$req = Requests::put($url . '/any');
	$tf->assertSame($req->body, 'ANY /any');

	$req = Requests::delete($url . '/any');
	$tf->assertSame($req->body, 'ANY /any');

	// $req = Requests::head($url . '/any');
	// $tf->assertSame($req->body, 'ANY /any');

	$req = Requests::get($url . '/space/');
	$tf->assertSame($req->body, 'GET /space/');

	$req = Requests::post($url . '/space/');
	$tf->assertSame($req->body, 'POST /space/');

	$req = Requests::get($url . '/_all');
	$tf->assertSame($req->body, 'all');

	$req = Requests::post($url . '/_all');
	$tf->assertSame($req->body, 'all');

	$req = Requests::post($url . '/lla');
	$tf->assertSame($req->body, 'all');

	$req = Requests::put($url . '/all');
	$tf->assertSame($req->body, 'all');
});
