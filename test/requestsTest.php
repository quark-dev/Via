<?php

$tf->test("Requests test", function($tf) {
	$url = URL . "requests.php";

	// @TODO
	// $req = Requests::get(URL . $file . '/');
	// $tf->assertSame($req->body, '');

	$req = Requests::get($url . '/123');
	$tf->assertSame($req->body, '123');

	// with special chars
	$req = Requests::get($url . '/qwe_-$=&+*\'');
	$tf->assertSame($req->body, 'qwe_-$=&+*\'');

	$req = Requests::get($url . '/a/123');
	$tf->assertSame($req->body, '123');

	$req = Requests::get($url . '/123/321');
	$tf->assertSame($req->body, '123321');

	$req = Requests::get($url . '/a/test/c');
	$tf->assertSame($req->body, 'test');

	$req = Requests::get($url . '/b/123/321');
	$tf->assertSame($req->body, '123321');

	$req = Requests::get($url . '/b/123/321?varTest=xxx');
	$tf->assertSame($req->body, '123xxx', 'test param()');

	$req = Requests::get($url . '/test/a/b/c/d/../..///._+*/-');
	$tf->assertSame($req->body, 'test');

	$req = Requests::get($url . '/c/abc?foo=bar&n=42');
	$tf->assertSame($req->body, 'bar42', 'test query');

	$req = Requests::get($url . '/path/foo/bar');
	$tf->assertSame($req->body, '/path/foo/bar', '$req->path');

	$req = Requests::get($url . '/path/foo/?foo=bar&1=1');
	$tf->assertSame($req->body, '/path/foo/', '$req->path (2)');

	$req = Requests::get($url . '/pathurl/foo/bar');
	$tf->assertSame($req->body, '/pathurl/foo/bar/pathurl/foo/bar', '$req->path . $req->url');

	$req = Requests::get($url . '/pathurl/foo?foo=bar&1=1');
	$tf->assertSame($req->body, '/pathurl/foo/pathurl/foo?foo=bar&1=1', '$req->path . $req->url (2)');

	$req = Requests::get($url . '/noname/foo/bar');
	$tf->assertSame($req->body, 'foobar', '$req->param(x) (without variable name)');

	$req = Requests::get($url . '/noname/foo/bar/a/b/c');
	$tf->assertSame($req->body, 'foobara', '$req->param(x) (without variable name) (2)');

	$req = Requests::get($url . '/is', ['accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8']);
	$tf->assertSame($req->body, '11', 'is()');
});
