<?php

namespace Via;

/**
 * Response
 *
 * @author Fabien Sa
 * @version 0.1.0
 * @package Via
 */
class Response {

	/**
	 * @var array Header fields
	 */
	private $header;

	/**
	 * @var string Output buffer
	 */
	private $buffer;

	public function __construct(array $options = array()) {
		// Override header
		$this->header = array();

		if(isset($options['x-powered-by']) && $options['x-powered-by'] === true) {
			$this->header['X-Powered-By'] = 'Via';
		}

		if(isset($options['header'])) {
			$this->header = $options['header'];
		}
	}

	/**
	 * Set status code
	 *
	 * @param int $code
	 * @return self
	 */
	public function status($code) {
		$this->header['__status'] = $code;

		return $this;
	}

	/**
	 * Download file
	 *
	 * @param string $path
	 * @param string (optional) $filename
	 * @param string (optional) $contentType Force specific content type
	 * @return bool|int False if file does not exists else return readfile($path)
	 */
	public function download($path, $filename = null, $contentType = 'application/octet-stream') {

		if(file_exists($path)) {
			if($filename === null) {
				$filename = basename($path);
			}

			header('Content-Description: File Transfer');
			header('Content-Type: ' . $contentType);
			header('Content-Disposition: attachment; filename=' . $filename);
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($path));

			return readfile($path);
			// exit;
		}

		return false;
	}

	/**
	 * Set header
	 *
	 * Examples:
	 *
	 *    $res->set('Accept', 'application/json');
	 *
	 * @param string|array $field
	 * @param string $value
	 * @return self
	 */
	public function set($field, $value) {
		if(is_array($field)) {
			foreach ($field as $key => $value) {
				$this->header[$key] = trim($value);
			}
		} else {
			$this->header[$field] = trim($value);
		}

		return $this;
	}

	/**
	 * Get header field
	 *
	 * @param string $field
	 * @return bool|string
	 */
	public function get($field) {
		if(isset($this->header[$field])) {
			return $this->header[$field];
		}

		return false;
	}

	/**
	 * Set content type
	 *
	 * Example:
	 *
	 *     $res->contentType("text/plain");
	 *
	 * @param string $type
	 * @return self
	 */
	public function contentType($type) {
		$this->header['Content-type'] = trim($type);

		return $this;
	}

	/**
	 * Read or write file
	 *
	 * Examples:
	 *
	 *     file('test.txt') // read file 'text.txt'
	 *     file('data.txt', $myData) // write $data inside 'data.txt'
	 *
	 * @param string $filename File name
	 * @param mixed $content Optional. The content to write. If no $content, the
	 * file is read
	 * @return boolean|string Return bool if write is ok or the content if
	 * file is read
	 */
	public function file($filename, $content = null) {
		if($content === null) {
			return file_get_contents($filename);
		} else {
			return file_put_contents($filename, $content);
		}
	}

	/**
	 * Redirect to the given url with optional status code
	 *
	 * @param int $status (optional) Status code
	 * @param string $url Given url for redirection
	 */
	public function redirect($status, $url = null) {
		if($url === null) {
			$url = $status;
			if($url === '') {
				$url = './';
			}
			header('Location: ' . $url);
		} else {
			header('Location: ' . $url, true, $status);
		}

		exit;
	}

	/**
	 * Set cookie
	 *
	 * Example:
	 *
	 *     $res->cookie("TestCookie", "something", ["maxAge" => 3600, "secure" => true]);
	 *
	 * @param string $name Name of the cookie
	 * @param string $value The value of the cookie
	 * @param array $options Array of options (expires, path, domain, secure, httpOnly, maxAge)
	 * @return bool
	 */
	public function cookie($name, $value = "", array $options = array()) {
		extract($options);

		isset($path) ?: $path = '/';

		isset($domain) ?: $domain = '';

		isset($secure) ?: $secure = false;

		isset($httpOnly) ?: $httpOnly = false;

		if(isset($maxAge)) {
			$expires = time() + $maxAge;
		} else {
			isset($expires) ?: $expires = 0;
		}

		return setcookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
	}

	/**
	 * Clear cookie name
	 *
	 * @param string $name Name of the cookie
	 * @param array $options Array of options (path, domain, secure, httpOnly)
	 * @return bool
	 */
	public function clearCookie($name, array $options = array()) {
		extract($options);

		isset($path) ?: $path = '/';

		isset($domain) ?: $domain = '';

		isset($secure) ?: $secure = false;

		isset($httpOnly) ?: $httpOnly = false;

		return setcookie($name, '', time() - 3600, $path, $domain, $secure, $httpOnly);
	}

	/**
	 * Send JSON response
	 *
	 * Examples:
	 *
	 *     $res->json("{ user: 'Mario' }");
	 *     $res->json(new User('Peach')); // serialize object to json
	 *
	 * @param int (optional) $status
	 * @param mixed $message
	 * @return Response
	 */
	public function json($message) {
		$this->contentType("application/json");

		if(2 === func_num_args()) {
			$status = $message;
			$message = func_get_arg(1);
		} else {
			$status = 200;
		}

		return $this->send($status, $message);
	}

	/**
	 * Send a response
	 *
	 * Example:
	 *     $res->send("Hello");
	 *     $res->send(404, "Ooops");
	 *     $res->send(new MyObject()); // will encode object to json
	 *
	 * @param int (optional) $status
	 * @param string $message
	 * @return Response
	 */
	public function send($message) {
		if(2 === func_num_args()) {
			$this->status($message);
			$message = func_get_arg(1);
		}

		if(!is_scalar($message)) {
			$message = json_encode($message);
		}

		$this->buffer .= $message;
		// $this->end();

		return $this;
	}

	/**
	 * Secure send
	 * Same as `send` but encode special chars
	 *
	 * @see send
	 */
	public function ssend($message) {
		$message = htmlspecialchars($message);

		if(2 === func_num_args()) {
			$arg1 = func_get_arg(1);
			return $this->send($message, $arg1);
		} else {
			return $this->send($message);
		}
	}

	/**
	 * Render a view
	 *
	 * @param string $view
	 * @param array (optional) $data
	 * @return string|bool False if file not found. Html data if file exists.
	 */
	public function render($view, array $data = array()) {
		extract($data);

		if(!file_exists($view)) {
			return false;
		}

		// output buffering
		ob_start();
		include_once $view;
		$buffer = ob_get_clean();

		return $buffer;
	}

	/**
	 * Set header and send buffer
	 *
	 * @param bool $printBuffer Echo buffer
	 * @return string Return the buffer
	 */
	public function end($printBuffer = true) {

		// Set header
		foreach ($this->header as $key => $value) {
			if($key === '__status') {
				header(' ', true, $value);
			}
			elseif(is_string($value)) {
				header($key . ": " . $value);
			}
		}

		if($printBuffer) {
			echo $this->buffer;
		}

		// $this->buffer = '';
		return $this->buffer;
	}

	/**
	 * Alias for `send`
	 *
	 * @see send
	 */
	public function __invoke($message) {
		if(2 === func_num_args()) {
			$arg1 = func_get_arg(1);
			return $this->send($message, $arg1);
		} else {
			return $this->send($message);
		}
	}

}
