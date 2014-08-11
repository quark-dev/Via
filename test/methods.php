<?php
require '_routerBootstrap.php';

$app->get('/', function() {
	echo 'GET /';
});

$app->post('/', function() {
	echo 'POST /';
});

$app->put('/', function() {
	echo 'PUT /';
});

$app->delete('/', function() {
	echo 'DELETE /';
});

$app->on('GET', '/on', function() {
	echo 'GET /on';
});

$app->on('PUT', '/on', function() {
	echo 'PUT /on';
});

$app->on(['PUT', 'GET', 'POST'], '/multi', function() {
	echo 'MULTI /multi';
});

$app->any('/any', function() {
	echo 'ANY /any';
});

$app->with('/space', function($app) {
	$app->get('/', function($req, $res) {
		echo 'GET /space/';
	});

	$app->post('/', function($req, $res) {
		echo 'POST /space/';
	});
});

$app->all(function($req, $res) {
	return 'all';
});

$app();
