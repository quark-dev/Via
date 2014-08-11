# Via

Via is a simple and scalable router inspired by [express](http://expressjs.com/).

This is the very first version, use with caution !

### Needed

 * PHP 5.3+
 * (optional) URL rewriting


### Installation with composer

 * `composer install via`



## Using Via

#### Simple route

```php
<?php
$app->get('/test', function($req, $res) {
	$res('Test OK'); // print 'Test OK'
	$res->send('Test OK'); // Same as above
});

$app(); // run the router
```

#### With parameters

```php
<?php
$app->post('/test/:var', function($req, $res) {
	$res($req('var'));
	$res($req->params->var)); // Same as above
});
```


#### Multiple methods

```php
<?php
$app->on(['GET', 'POST'], '/test', function() {
	// ...
});
```


#### Match multiple routes

```php
<?php
// Match both (print '12')
$app->get('/test', function($req, $res, $next) {
	$res('1');
	$next(); // Continue to test next routes
});

$app->get('/test', function($req, $res) {
	$res('2');
});
```


#### Using namespaces

```php
<?php
$app->with('/sub', function($app) {

	$app->with('/a', function($app) {
		// inside /sub/a

		$app->get('/test', function($req, $res){
			// match /sub/a/test
		});
	});
});
```


#### Use basic Via engine

```php
<?php
$app->get('/test', function($req, $res, $next) {
	// You can use $title inside view.html
	$html = $res->render('view.html', ['title' => 'My Title']);
	$res->send($html);
});
```



## Advanced usage

#### `Using` function will call $next() automatically

```php
<?php
$app->using(function($req, $res) {
	$res->contentType('text/plain');
	$res->set('X-Custom-Header', 'test');
	// will continue to next route
});

// ...
```


#### With specific method

```php
<?php
class A {
	function b($req, $res) {...}
}

$app->get('/', 'A@b');
```


#### Handle a controller

```php
<?php
class MyController {
	function getUser($req, $res) {...}

	function postUser($req, $res) {...}
}

$app->get('/test/@@', 'MyController');

// GET /test/user will call "getUser"
// POST /test/user will call "postUser"
```


---


# Documentation

## Router (app)

#### get(string $route, function $callback)
Add a route with GET verb

#### post(string $route, function $callback)
Add a route with POST verb

#### put(string $route, function $callback)
Add a route with PUT verb

#### delete(string $route, function $callback)
Add a route with DELETE verb

#### on(string|array $verb, string $route, function $callback)
Add a route (can use muliple verb)

#### all(function $callback)
Add a route that always match

#### using(function $callback)
Add a route that always match and go to next

#### with(string $namespace, function $callback)
Add a namespace (prefix all routes)

#### get(string $name)
Get value from container

#### set(string $name, mixed $value)
Set value in app container

#### render(string $view, array $data)
Render a view

 - `$res->render("user.html", ["title" => "User"])` // In template we can use: <title><?=$title?></title>



## Request

### Fields

 - `$params` Parameters mapped from URL
 - `$query` Queries mapped from URL
 - `$cookies` Cookies
 - `$body` Proprieties from POST
 - `$ip` Get remote IP address
 - `$path` Get requested URL path
 - `$xhr` Check if request was done with 'XMLHttpRequest'
 - `$verb` HTTP verb (method)
 - `$url` Get current URL


#### get(string $field)
Get a specific header

- `$req->header('host');`
- `$req->header(...);` alias

#### param(string $name)
Return if exists (in order) $req->params->$name or $req->body->$name or $req->query->$name

 - `$req->param('str')`
 - `$req(...);` alias

#### is(string $type)
Check if mime type is in header

 - `$req->is('html');`

#### [static] getRelativeURIToRoot()
Return path relative to root (eg. useful for assets in views)



## Response

#### send([int $status], string $message)
Send output buffer

 - `$res->send('text');`
 - `$res->send(404, 'Not found');`
 - `$res->send(new MyObj());` print json
 - `$res(...);` alias of `$res->send(...);`

#### json([int $status], string $json)
Send output buffer and force json content-type

 - `$res->json("{ user: 'Mario' }");`

#### redirect([int $status], string $url)
Redirect client to specific url

 - `$res->redirect('http://cern.ch');`
 - `$res->redirect(301, '/test');`

#### download(string $path, [string $filename, string $contentType])
Force to download a specific file

 - `$res->download('myImage.png');`
 - `$res->download('CV_en_v4.pdf', 'Smith_CV.pdf');`

#### file($filename, [$content])
Read or write file

 - `$res->file('myImage.png');` Read file
 - `$res->file('file.txt', $data);` Write data

#### contentType(string $type)
 - `$res->contentType('text/plain');` Set 'Content-Type' header to text/plain

#### set(string $field, string $value)
Set header parameters

 - `$res->set('Content-Type', 'text/plain');` Set 'Content-Type' header to text/plain
 - ```$res->set([
	  'Content-Type' => 'text/plain',
	  'ETag' => '10000'
	]);```

#### status(int $code)
 - `$res->status(404);`

#### cookie(string $name, [string $value, array $options])
 -	`$res->cookie("TestCookie", "something", ["maxAge" => 3600, "secure" => true]);`

#### clearCookie(string $name, [array $options])
 -	`$res->clearCookie("TestCookie");` Clear 'TestCookie' cookie

#### render(string $view, array $data)
Alias of render in "Router"

