<?php

namespace Via;

/**
 * Space
 * Represent a namespace
 *
 * @author Fabien Sa
 * @version 0.1.0
 * @package Via
 */
class Space implements RoutableInterface {

	/**
	 * @var string Client path
	 */
	private $path;

	private $callback;

	/**
	 * Space constructor
	 *
	 * @param string $path
	 * @param function ? $callbacks
	 */
	function __construct($path, $callback) {
		$this->path = $path;
		$this->callback = $callback;
	}

	/**
	 * Test if path match url
	 *
	 * @return mixed
	 */
	public function match($verb, $path) {

		$pathRegex = $this->path . "/?";

		// delete delimiters
		$pathRegex = str_replace('#', '', $pathRegex);

		if(preg_match('#^' . $pathRegex . '#', $path, $matches) !== 0) {
			// echo "[ SPACE MATCH `$path` ]\n";
			return $this;
		}

		return false;
	}

	/**
	 * Get path
	 *
	 * @return string Path
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Get verb
	 *
	 * @return bool
	 */
	function getVerb() {
		return false;
	}

	/**
	 * Get callback
	 *
	 * @return function
	 */
	public function getCallback() {
		return $this->callback;
	}

}
