<?php
require '_routerBootstrap.php';

$app->get('/path/*', function($req, $res) {
	$res->send($req->path);
});

$app->get('/pathurl/*', function($req, $res) {
	$res->send($req->path . $req->url);
});

$app->get('/is', function($req, $res) {
	$res->send($req->is("html"));
	$res->send($req->is("xml"));
});

$app->get('/c/*', function($req, $res) {
	$res->send($req->query->foo . $req->query->n);
});

$app->get('/noname/*', function($req, $res) {
	$res->send($req->param(0) . $req->param(1) . $req(2));
});

$app->get('/:v1', function($req, $res) {
	$res->send($req->params->v1);
});

// buggy url
$app->get('/a/:v1?#\\#', function($req, $res) {
	$res->send($req->params->v1);
});

$app->get('/:v1/:v2', function($req, $res) {
	$res->send($req->params->v1);
	$res->send($req->params->v2);
});

$app->get('/b/:v1/:varTest', function($req, $res) {
	// alias
	$res->send($req('v1'));
	$res->send($req('varTest'));
});

$app->get('/a/:v1/c', function($req, $res) {
	$res->send($req->params->v1);
});

$app->get('/:v1/*', function($req, $res) {
	$res->send($req->params->v1);
});

$app();
