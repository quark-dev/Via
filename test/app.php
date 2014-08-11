<?php
require '_routerBootstrap.php';

$app = new Via\Router(array(
	'x-powered-by' => false
));

$app->get('/1', function($req, $res, $next) {
	$res->send("test");
});


// Test container
$app->set("k", "123");
$app->get('/app/1', function($req, $res, $next) use($app) {
	$res->send($app->get("k"));
});

$app();



$app2 = new Via\Router();

$app2->get('/2', function($req, $res, $next) {
	$res->send("test");
});

$app2();
