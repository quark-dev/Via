<?php

namespace Via;

/**
 * Routable
 *
 * @author Fabien Sa
 * @version 0.1.0
 * @package Via
 */
interface RoutableInterface {

	public function match($method, $path);

	public function getPath();

	public function getVerb();

	public function getCallback();
}
