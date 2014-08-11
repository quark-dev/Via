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

$fn = function($req, $res) {
	$res->send('/fn');
};
$app->get('/fn', $fn);


// regexes
$app->get('R/r/[a-zA-Z]+', function($req, $res) {
	$res->send('regex [a-zA-Z]+');
});

$app->get('R/r/([a-zA-Z_-]+)', function($req, $res) {
	$res->send('regex ([a-zA-Z_-]+) ' . $req->params->{0});
});

$app->get('R/r/.+', function($req, $res) {
	$res->send('regex .+');
});


class Ctrl {
	function getMethod($req, $res) {
		$res("GET test");
	}

	function gEtMeThOd2($req, $res) {
		$res("GET test2");
	}

	function POSTMethod($req, $res) {
		$res("POST test");
	}

	function putMethod($req, $res) {
		$res("PUT test");
	}

	function method($req, $res) {
		$res("method");
	}

	function methodNext($req, $res, $next) {
		$res("methodNext");
		$next();
	}

	function method2($req, $res) {
		return "method2";
	}
}

$app->get('/c/0', 'Ctrl@method');
$app->get('/c/1', 'Ctrl@methodNext');
$app->get('/c/1', 'Ctrl@method2');

$app->get('/c/2/@@', 'Ctrl');

$app->get('/c/3/@@/sufix', 'Ctrl');

$app->any('/c/4/@@', 'Ctrl');



$app->all(function($req, $res) {
	$res->send('all');
});

$app();
