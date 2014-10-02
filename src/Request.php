<?php

namespace Via;

/**
 * Request
 *
 * @author Fabien Sa
 * @version 0.1.0
 * @package Via
 */
class Request {

	/**
	 * @var \StdClass Parameters mapped from URL
	 */
	public $params;

	/**
	 * @var \StdClass Queries mapped from URL
	 */
	public $query;

	/**
	 * @var \StdClass Cookies
	 */
	public $cookies;

	/**
	 * @var \StdClass Proprieties from POST
	 */
	public $body;

	/**
	 * @var \StdClass Headers from server
	 */
	private $header;

	/**
	 * @var string Get remote IP address
	 */
	public $ip;

	/**
	 * @var string Get requested URL path
	 */
	public $path;

	/**
	 * @var bool
	 */
	public $xhr;

	/**
	 * @var string HTTP verb (method)
	 */
	public $verb;

	/**
	 * @var string Get current URL
	 */
	public $url;

	/**
	 * @var string Get current URL (with host)
	 */
	public $fullUrl;

	/**
	 * Constructor
	 *
	 * @param array Options
	 */
	public function __construct(array $options = array()) {
		if(!isset($options['method'])) {
			if(isset($_SERVER['REQUEST_METHOD'])) {
				$this->verb = strtoupper($_SERVER['REQUEST_METHOD']);
			} else {
				$this->verb = '/';
			}
		} else {
			$this->verb = strtoupper($options['method']);
		}

		$scriptName = '';
		if(isset($_SERVER['SCRIPT_NAME'])) {
			$scriptName = $_SERVER['SCRIPT_NAME'];
		}

		if(isset($_SERVER['REQUEST_URI'])) {
			$this->url = implode(explode($scriptName, $_SERVER['REQUEST_URI'], 2));

			// if rewriteEngine
			if(isset($_SERVER['REDIRECT_URL'])) {
				$root = dirname($_SERVER['SCRIPT_NAME']);
				$len = strlen($root);
				$this->url = substr($this->url, $len);
			}
		} else {
			$this->url = '/';
		}

		$this->fullUrl = $scriptName . $this->url;


		if(!isset($options['path'])) {
			if(!isset($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'])) {
				$this->path = '/';
			} else {

				// Check if exist query
				$pos = strpos($this->url, '?');
				if ($pos !== false) {
					$this->path = substr($this->url, 0, $pos);
					$this->query = substr($this->url, $pos + 1);

					// Parse queries
					$arr = array();
					parse_str($this->query, $arr);
					$this->query = (object) $arr;
				} else {
					$this->path = $this->url;
					$this->query = new \StdClass;
				}
			}
		} else {
			$this->path = $options['path'];
		}

		// Set header
		//@TODO getServerParam() ? NULL if empty
		$header = new \StdClass;
		if(isset($_SERVER)) {
			$header->host = @$_SERVER['HTTP_HOST'];
			$header->userAgent = @$_SERVER['HTTP_USER_AGENT'];
			$header->accept = @$_SERVER['HTTP_ACCEPT'];
			$header->acceptLanguage = @$_SERVER['HTTP_ACCEPT_LANGUAGE'];
			$header->acceptEncoding = @$_SERVER['HTTP_ACCEPT_ENCODING'];
			$header->dnt = @$_SERVER['HTTP_DNT'];
			$header->cookie = @$_SERVER['HTTP_COOKIE'];
			$header->connection = @$_SERVER['HTTP_CONNECTION'];
			$header->cacheControl = @$_SERVER['HTTP_CACHE_CONTROL'];
		}
		$this->header = $header;

		// Set IP
		if(isset($_SERVER['REMOTE_ADDR'])) {
			$this->ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$this->ip = "0.0.0.0";
		}

		// Set xhr
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
			$this->xhr = true;
		} else {
			$this->xhr = false;
		}

		// Set cookies
		$this->cookies = new \StdClass;
		if(isset($_COOKIE)) {
			$this->cookies = (object) $_COOKIE;
		}

		// Parsed request body
		$phpInput = file_get_contents("php://input", "r");
		if(!empty($phpInput)) {
			parse_str($phpInput, $body);
			$body = (object)$body;
			$this->body = $body;
		} else {
			$this->body = null;
		}
	}

	/**
	 * Parse parameters (typically after regexp)
	 * Resplit parameter if '/' is detected and clean doubles
	 *
	 * @param array $matches
	 * @return \StdClass
	 */
	public function parseParams(array $matches) {
		if(!empty($matches)) {
			$params = new \StdClass;
			$i = 0;
			$j = 0;
			foreach ($matches as $key => $value) {
				if(!is_int($key)) {
					$params->$key = $value;
					array_shift($matches);
				} else {
					// Stock full match
					$params->{"__{$j}"} = $value;
					++$j;

					// Re explode /
					if(strlen($value) > 1) {
						$subs = explode('/', $value);
					} else {
						$subs[0] = $value;
					}

					foreach($subs as $sub) {
						if($sub !== '') {
							$params->{$i++} = $sub;
						}
					}
				}
			}

			$this->params = $params;
			return $params;
		}
	}

	/**
	 * Return request header.
	 *
	 * @param string $field
	 * @return string|bool
	 */
	public function header($field) {

		$field = str_replace('-', '', strtolower($field));

		if(isset($this->header->{$field})) {
			return $this->header->{$field};
		} else {
			return false;
		}
	}

	/**
	 * Get
	 * alias of `header`
	 *
	 * @param string $field
	 * @return string|bool
	 */
	public function get($field) {
		return $this->header($field);
	}

	/**
	 * Check if mime type is in header
	 *
	 * Example:
	 *
	 *     $req->is('html');
	 *
	 * @param string $type
	 * @return bool If mime type is present
	 */
	public function is($type) {
		return preg_match('/' . preg_quote($type, '/') . '/i', $this->header->accept);
	}

	/**
	 * Check all parameters
	 *
	 * @param string $name
	 * @return string
	 */
	public function param($name) {
		if(isset($this->query->{$name})) {
			return $this->query->{$name};
		}
		else if(isset($this->body->{$name})) {
			return $this->body->{$name};
		}
		else if(isset($this->params->{$name})) {
			return $this->params->{$name};
		}
	}

	/**
	 * Alias to `param`
	 *
	 * @param string $name
	 * @return string
	 */
	public function __invoke($name) {
		return $this->param($name);
	}

	/**
	 * Get current URI
	 *
	 * @return string
	 */
	public static function getURI() {
		//TODO stock URI in field (same for relativeURI)
		$scriptName = $_SERVER['SCRIPT_NAME'];
		if(substr($_SERVER['REQUEST_URI'], 0, strlen($scriptName)) !== $scriptName) {
			$scriptName = dirname($scriptName);
		}

		return implode(explode($scriptName, $_SERVER['REQUEST_URI'], 2));
	}

	/**
	 * Check if Rewrite engine is active (if script.php is detected)
	 *
	 * @return bool If rewrite engine is active
	 */
	public static function isRewriteActive() {
		$exp = explode($_SERVER['SCRIPT_NAME'], $_SERVER['REQUEST_URI'], 2);

		return !isset($exp[1]);
	}

	/**
	 * Get relative URI from the root
	 *
	 * @return string
	 */
	public static function getRelativeURIToRoot($moreSlash = 0) {
		$postUrl = static::getURI();
		$postUrl = str_replace(array('//', '\\'), '/', $postUrl);

		$slashNb = substr_count($postUrl, '/');
		// echo $slashNb;
		return str_repeat('../', max($slashNb + $moreSlash - (int)static::isRewriteActive(), 0));
	}

}
