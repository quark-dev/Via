<?php

$tf->test("Response test", function($tf) {
	$url = URL . "response.php";

	$req = Requests::get($url . '/send/400');
	$tf->assertSame($req->status_code, 400, 'test if status == 400');
	$tf->assertSame($req->body, 'test', 'is body `test`');

	$req = Requests::get($url . '/content-type/1');
	$tf->assertSame($req->headers["content-type"], 'text/plain', 'if content-type is plain');
	$tf->assertSame($req->body, '123', 'is body `123`');

	$req = Requests::get($url . '/json');
	$tf->assertSame($req->headers["content-type"], 'application/json', 'if content-type is json');
	$tf->assertSame($req->body, '{"k":"val","0":"abc"}', 'json body');

	$req = Requests::get($url . '/json/400');
	$tf->assertSame($req->headers["content-type"], 'application/json', 'if content-type is json');
	$tf->assertSame($req->status_code, 400, 'test if status == 400');
	$tf->assertSame($req->body, '{"k":"val","0":"abc"}', 'json body');

	$req = Requests::get($url . '/status/1');
	$tf->assertSame($req->headers["content-type"], 'application/json', 'if content-type is json');
	$tf->assertSame($req->status_code, 200, 'test if status == 200');
	$tf->assertSame($req->body, '1{}', 'json body');

	$req = Requests::get($url . '/set');
	$tf->assertSame($req->headers["content-type"], 'text/plain', 'if content-type is plain');
	$tf->assertSame($req->headers["etag"], '10000', 'etags');
	$tf->assertSame($req->headers["X-Test"], '1', 'X-Test');

	$req = Requests::get($url . '/redirect');
	$tf->assertSame($req->body, 'new', 'new body after redirection');

	$req = Requests::get($url . '/using');
	$tf->assertSame($req->body, 'using', 'with `using` function (set headers)');
	$tf->assertSame($req->headers["content-type"], 'text/plain', 'if content-type is plain');
	$tf->assertSame($req->headers["X-Custom-Header"], 'test', 'if X-Custom-Header is equal to test');

	$req = Requests::get($url . '/download');
	$hex = bin2hex($req->body);
	// png magic number is 89 50 4e 47 0d 0a 1a 0a
	$tf->assertSame(substr($hex, 0, 16), '89504e470d0a1a0a', 'check png magic number');
});
