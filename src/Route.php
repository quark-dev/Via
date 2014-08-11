<?php

namespace Via;

/**
 * Route
 * Represent a route
 *
 * @author Fabien Sa
 * @version 0.1.0
 * @package Via
 */
class Route implements RoutableInterface {

	/**
	 * @var string Client path
	 */
	private $path;

	/**
	 * @var string Verb
	 */
	private $verb;

	/**
	 * @var array|function ?
	 */
	private $callback;

	/**
	 * Route constructor
	 *
	 * @param string $verb
	 * @param string $path
	 * @param function $callbacks
	 * @param array $options
	 */
	function __construct($verb, $path, $callback, $options = array()) {
		$this->path = $path;
		$this->verb = $verb;
		$this->callback = $callback;
	}

	/**
	 * Check if current route match client url & verb
	 *
	 * @param string $verb Method name (GET, POST, ...)
	 * @param string $path Path
	 */
	public function match($verb, $path) {

		// Special path, allways OK
		if($this->path === true) {
			return array();
		}

		// echo "\n$path - {$this->path} <br>|\n";
		if($this->matchMethod($verb)) {

			// echo $this->path."||\n ";
			$patternRegex = $this->normalizeRegex($this->path);

			// echo "|".$patternRegex."<br>\n\n";
			if (preg_match('#^' . $patternRegex . '$#', $path, $matches) !== 0) {
				// echo "__[Route MATCH {$this->path}]\n";

				if(isset($this->namespace)) {
					return $that->run(false);
				}

				array_shift($matches);

				return $matches;
			}
		}

		return false;
	}

	/**
	 * Check if verb match
	 *
	 * @param string $verb
	 * @return bool
	 */
	public function matchMethod($verb) {
		if(is_string($this->verb)) {
			return $verb === strtoupper($this->verb) || trim($this->verb, '#*') === '';
		} else {
			foreach ($this->verb as $val) {
				if($verb === strtoupper($val)) {
					return true;
				}
			}

		}

		return false;
	}

	/**
	 * Normalize regexp pattern
	 *
	 * @param string $pattern Pattern to normalize
	 * @return string Normalised regexp pattern
	 */
	private function normalizeRegex($pattern) {
		$pattern .= '/?'; // like express

		// Create regex route
		if(strlen($pattern) > 0 && strtoupper($pattern[0]) !== 'R') {

			// Delete delimiters
			$pattern = str_replace('#', '', $pattern);

			$pattern = preg_replace(
				'#\((\w+?)\)#',
				'(?:$1)',
				($pattern)
			);

			// Aliases
			$pattern = str_replace('.', '\.', $pattern);
			$pattern = str_replace('*', '(.*)', $pattern);
			// $pattern = str_replace('**', '(.*)', $pattern);
			// $pattern = str_replace('*', '([^/]*)', $pattern);
			$pattern = str_replace('+', '(.+)', $pattern);
			$pattern = str_replace('@@', '(?P<__method__>[^\/]+)?', $pattern);
			$pattern = str_replace('/?', '/{0,1}', $pattern);
		} else {
			$pattern = substr($pattern, 1);
		}

		$patternRegexp = preg_replace('/\:([a-zA-Z_]\w*)/', '(?P<$1>[^\/]+)?', $pattern);

		return $patternRegexp;
	}

	/**
	 * Matches callback for respond function
	 *
	 * @param array Matches
	 * @return string Regexp with group(s)
	 */
	private function matchesCallback($m) {
		return '(?P<' . $m[1] . '>[^/]+?)?';
	}

	/**
	 * Get path
	 *
	 * @return string Path
	 */
	function getPath() {
		return $this->path;
	}

	/**
	 * Get verb
	 *
	 * @return string Verb
	 */
	function getVerb() {
		return $this->verb;
	}

	/**
	 * Get callback
	 *
	 * @return function
	 */
	function getCallback() {
		return $this->callback;
	}

}
