<?php

$tf->test("App", function($tf) {
	$url = URL . "app.php";

	$req = Requests::get($url . '/1');
	$tf->assertNotSame($req->headers["x-powered-by"], 'Via', 'x-powered-by 1');
	$tf->assertSame($req->body, "test", 'body');

	$req = Requests::get($url . '/2');
	$tf->assertSame($req->headers["x-powered-by"], 'Via', 'x-powered-by 2');
	$tf->assertSame($req->body, "test", 'body 2');

	$req = Requests::get($url . '/app/1');
	$tf->assertSame($req->body, "123", 'container');

});
