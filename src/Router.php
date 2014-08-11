<?php

namespace Via;

use Via\Exception;

/**
 * Router
 *
 * @author Fabien Sa
 * @version 0.1.0
 * @package Via
 */
class Router {

	/**
	 * @var \SplDoublyLinkedList All routes
	 */
	public $routes;

	/**
	 * @var string Current namespace
	 */
	private $namespace;

	/**
	 * @var array Container
	 */
	private $container;

	/**
	 * @var Request Request object
	 */
	private $request;

	/**
	 * @var Response Response object
	 */
	private $response;

	/**
	 * @var array Settings
	 */
	private $settings;

	/**
	 * @var bool If response is send
	 */
	private $isResponseSend = false;

	/**
	 * Router constructor
	 *
	 * options: path, method, viewPrefix, viewSuffix, x-powered-by
	 *
	 * @param array $options
	 */
	function __construct(array $options = array()) {

		$defaultOptions = array(
				'x-powered-by' => true
			);

		$this->settings = array_merge($defaultOptions, $options);
		$this->namespace = '';
		$this->routes = new \SplDoublyLinkedList;
	}

	/**
	 * Add a new route with method "GET"
	 *
	 * @param string $route Route to match
	 * @param \Closure|string $callback Callback
	 * @return self
	 */
	public function get($route, $callback = null) {
		if($callback === null) {
			return $this->getValue($route);
		}

		return $this->on('GET', $route, $callback);
	}

	/**
	 * Add a new route with method "POST"
	 *
	 * @param string $route Route to match
	 * @param \Closure|string $callback Callback
	 * @return self
	 */
	public function post($route, $callback) {
		return $this->on('POST', $route, $callback);
	}

	/**
	 * Add a new route with "DELETE" method
	 *
	 * @param string $path
	 * @param \Closure|string $callback
	 * @return self
	 */
	public function delete($route, $callback) {
		return $this->on('DELETE', $route, $callback);
	}

	/**
	 * Add a new route with "PUT" method
	 *
	 * @param string $path
	 * @param \Closure|string $callback
	 * @return Via
	 */
	public function put($route, $callback) {
		return $this->on('PUT', $route, $callback);
	}

	/**
	 * Add a new route for any methods
	 *
	 * @param string $path
	 * @param \Closure|string $callback
	 * @return Via
	 */
	public function any($route, $callback) {
		return $this->on('', $route, $callback);
	}

	/**
	 * Match all url
	 *
	 * @param \Closure|string $callback
	 * @return Via
	 */
	public function all($callback) {
		return $this->on('', true, $callback);
	}

	/**
	 * Using all url and go to next route
	 *
	 * @param \Closure|string $callback
	 * @return Via
	 */
	public function using($callback) {
		return $this->on('_next_', true, $callback);
	}

	/**
	 * Add a new route
	 *
	 * @param string|array $verb Method(s) to match
	 * @param string $route Route to match
	 * @param \Closure|string $callback Callback
	 * @return Via
	 */
	public function on($verb, $path, $callback) {

		if($path !== true) {
			$path = $this->namespace . $path;
		}

		$this->routes->push(new Route(
			$verb,
			$path,
			$callback
		));

		return $this;
	}

	/**
	 * Namespace for requests
	 *
	 * @param string $namespace
	 * @param \Closure $callback Callback
	 * @return Via
	 */
	public function with($namespace, \Closure $callback) {

		$this->routes->push(new Space($this->namespace . $namespace, $callback));

		return $this;
	}

	/**
	 * Run the router
	 *
	 * @param bool (optional) $rewind
	 * @return bool If a route has match
	 */
	public function run($rewind = true) {

		isset($this->request) ?: $this->request = new Request($this->settings);
		isset($this->response) ?: $this->response = new Response($this->settings);

		$routes = $this->routes;

		if($rewind === true) {
			$routes->rewind();
		} else {
			$routes->next();
		}

		for(; $routes->valid(); $routes->next()) {
			$route = $routes->current();

			if($this->dispatch($route)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Dispatch route
	 *
	 * @param Via\RoutableInterface $route Route to dispatch
	 * @return mixed
	 */
	private function dispatch(RoutableInterface $route) {
		$className = basename(str_replace('\\', '/', get_class($route)));

		if(false !== ($params = $route->match($this->request->verb, $this->request->path))) {
			$fn = $route->getCallback();

			if($className === 'Space') {
				$that = clone $this;
				$that->namespace = $route->getPath();
				$that->routes = new \SplDoublyLinkedList;

				echo $fn($that);
				$that->run();

				return true;

			} else {
				$this->request->parseParams($params);

				$that = $this;
				$next = function() use ($that) {
					return $that->run(false);
				};

				// Call a controller
				if(is_string($fn)) {
					$fn = $this->getCallbackFromController($fn);
				}

				$res = $fn($this->request, $this->response, $next);

				if($route->getVerb() === '_next_') {
					return false;
				}

				if(!$this->isResponseSend) {
					$this->response->end();
					$this->isResponseSend = true;
				}

				if($res !== null) {

					if(!is_scalar($res)) {
						$res = json_encode($res);
					}

					echo $res;
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Get callback from controller class
	 *
	 * Example:
	 *
	 *     `MyController` will handle "MyController" class as a controller
	 *     `MyController@test` will call "test" method in "MyController" class
	 *
	 * @param string $controller Controller name
	 * @throws \Via\Exception\ClassNotFoundException If class is not found
	 * @throws \Via\Exception\MethodNotFoundException If method is not found
	 * @return \Closure Create closure from controller method
	 */
	private function getCallbackFromController($controller) {
		if(strpos($controller, '@') !== false) {
			// Specific method in controller
			$info = explode('@', $controller);

			$controller = $info[0];
			$methodName = $info[1];
		} else {
			// Global controller
			$methodName = $this->request->params->__method__;
			$methodName = $this->request->verb . $methodName;
		}

		if(!class_exists($controller)) {
			throw new Exception\ClassNotFoundException("Class `{$controller}` not found.");
		}

		$instance = new $controller;

		if(!method_exists($instance, $methodName)) {
			throw new Exception\MethodNotFoundException(
				"Method `{$methodName}` not found in class `{$controller}`.");
		}

		return function($request, $response, $next) use($instance, $methodName) {
			return $instance->$methodName($request, $response, $next);
		};
	}

	// ??
	public function redirect($url) {
		// $routes->rewind();
		// $this->request = new Request($options);
		$this->request->path = $url;
		$this->run();
	}

	/**
	 * Render a view
	 *
	 * @param string $view View to render
	 * @param array $data Data to extract inside view
	 * @return string Rendered html
	 */
	public function render($view, array $data = array()) {

		if(isset($this->settings['viewPrefix'])) {
			$view = $this->settings['viewPrefix'] . $view;
		}

		if(isset($this->settings['viewSuffix'])) {
			$view .= $this->settings['viewSuffix'];
		}

		return $this->response->render($view, $data);
	}

	/**
	 * Get value from name
	 *
	 * @param mixed $name
	 * @return mixed variable name
	 */
	public function getValue($name) {
		if(isset($this->container[$name])) {
			return $this->container[$name];
		}
	}

	/**
	 * Set value
	 *
	 * @param string|int $name
	 * @param mixed $value
	 * @return self
	 */
	public function set($name, $value) {
		$this->container[$name] = $value;

		return $this;
	}

	/**
	 * Alias for `run()`
	 *
	 * @return mixed
	 */
	public function __invoke() {
		return $this->run();
	}

}
