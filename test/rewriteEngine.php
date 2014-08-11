<?php

require '_routerBootstrap.php';

$app->get('', function($req, $res, $next) {
	$res->send("''");
	$next();
});

$app->get('/', function($req, $res) {
	$res->send('/');
});

$app->get('/test/*', function($req, $res) {
	$res->send('/test/*');
});

$app->get('/*/test', function($req, $res) {
	$res->send('/*/test');
});

$app->with('/space', function($app) {
	$app->get('', function($req, $res) {
		$res->send('/space');
	});

	$app->get('/', function($req, $res) {
		$res->send('/space/');
	});

	$app->with('/42', function($app) {
		$app->get('', function($req, $res) {
			$res->send('/space/42');
		});

		$app->get('/end', function($req, $res) {
			$res->send('/space/42/end');
		});
	});

	$app->get('*', function($req, $res) {
		$res->send('/space/*');
	});
});

$app();
