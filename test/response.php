<?php
require '_routerBootstrap.php';

$app->get('/send/400', function($req, $res) {
	$res->send(400, 'test');
});

$app->get('/content-type/1', function($req, $res) {
	$res->send('1')->set('Content-Type', 'text/plain');

	$res('2'); // alias for $res->send('2');

	return '3';
});

$app->get('/json', function($req, $res) {
	$json = json_encode(['k' => 'val', 'abc']);
	$res->json($json);
});

$app->get('/json/400', function($req, $res) {
	$json = json_encode(['k' => 'val', 'abc']);
	$res->json(400, $json);
});

$app->get('/status/1', function($req, $res) {
	$res(400, '1'); // alias for $res->send(400, '1');

	$res->contentType('text/plain')->json('{}'); // override status and content-type
});

$app->get('/redirect', function($req, $res) {
	$res->redirect('new');
});

$app->get('/new', function($req, $res) {
	$res->send('new');
});

$app->get('/download', function($req, $res) {
	$res->download('img.png');
});

$app->get('/set', function($req, $res) {
	$res->set([
	  'Content-Type' => 'text/plain',
	  'Content-Length' => '100',
	  'ETag' => '10000',
	  'X-Test' => '1'
	]);
});

$app->using(function($req, $res) {
	$res->contentType('text/plain');
	$res->set('X-Custom-Header', 'test');
});

$app->get('/using', function($req, $res) {
	$res->send('using');
});

$app->get('/download', function($req, $res) {
	$res->download('img.png');
});

$app->get('/cookie', function($req, $res) {
	$res->cookie("test", "lol", ["path" => "/img"]);
});

$app();
