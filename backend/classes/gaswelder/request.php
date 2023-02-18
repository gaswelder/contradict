<?php

namespace gaswelder;

use \Exception;

class request
{
	private static $init = false;
	private static $post = [];
	private static $get = [];

	/**
	 * Returns request's payload parsed as JSON.
	 */
	static function json()
	{
		return json_decode(self::body(), true);
	}

	/**
	 * Returns the request's method.
	 */
	static function method(): string
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	static function get($key)
	{
		self::init();
		if (array_key_exists($key, self::$get)) {
			return self::$get[$key];
		} else {
			return null;
		}
	}

	/**
	 * Returns value of the given POST field.
	 *
	 * @param string $key
	 * @return string|array|null
	 */
	static function post($key)
	{
		self::init();
		if (array_key_exists($key, self::$post)) {
			return self::$post[$key];
		} else {
			return null;
		}
	}

	static function posts($_keys_)
	{
		$keys = func_get_args();
		$data = array();
		foreach ($keys as $k) {
			$data[$k] = self::post($k);
		}
		return $data;
	}

	static function header($name)
	{
		// getallheaders is not available on all servers.
		$key = 'HTTP_' . str_replace('-', '_', strtoupper($name));
		if (isset($_SERVER[$key])) {
			return $_SERVER[$key];
		}
		return null;
	}

	private static function init()
	{
		if (self::$init) {
			return;
		}
		self::$init = true;

		self::$post = [];
		self::$get = [];

		foreach ($_POST as $k => $v) {
			self::$post[$k] = $v;
		}
		foreach ($_GET as $k => $v) {
			self::$get[$k] = $v;
		}
	}

	static function url()
	{
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$protocol = "https";
		} else {
			$protocol = "http";
		}
		$domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
		return new url($domain . $_SERVER['REQUEST_URI']);
	}

	/**
	 * Returns contents of $_FILES normalized as array.
	 */
	private static function php_files($input_name)
	{
		if (!isset($_FILES[$input_name])) {
			return [];
		}
		if (!is_array($_FILES[$input_name]['name'])) {
			return [$_FILES[$input_name]];
		}

		$files = [];
		$fields = [
			"type",
			"tmp_name",
			"error",
			"size",
			"name"
		];
		foreach ($_FILES[$input_name]['name'] as $i => $name) {
			$input = [];
			foreach ($fields as $f) {
				$input[$f] = $_FILES[$input_name][$f][$i];
			}
			$files[] = $input;
		}
		return $files;
	}

	/**
	 * Returns files that were uploaded without errors.
	 * 
	 * @return array
	 */
	static function uploads($input_name)
	{
		$uploads = [];
		foreach (self::php_files($input_name) as $file) {
			// Happens with multiple file inputs with the same name marked with '[]'.
			if ($file['error'] == UPLOAD_ERR_NO_FILE) {
				continue;
			}
			if ($file['error'] || !$file['size']) {
				continue;
			}
			unset($file['error']);
			$uploads[] = new upload($file);
		}
		return $uploads;
	}

	/**
	 * Returns request's body as a string.
	 *
	 * @return string
	 */
	static function body()
	{
		return file_get_contents('php://input');
	}
}

class url
{
	private $data;
	private $original;

	function __construct($url)
	{
		$this->original = $url;
		$this->data = parse_url($url);

		$domain = "$this->scheme://$this->host";
		if ($this->port) {
			$domain .= ":$this->port";
		}
		$this->data['domain'] = $domain;
	}

	function __toString()
	{
		return $this->original;
	}

	function __get($k)
	{
		return isset($this->data[$k]) ? $this->data[$k] : null;
	}

	function isUnder($prefix)
	{
		$path = $this->path;
		return $path == $prefix || strpos($path, $prefix . '/') === 0;
	}
}

class upload
{
	private $info;

	function __construct($info)
	{
		$this->info = $info;
	}

	/**
	 * Returns the file's body as string.
	 *
	 * @return string
	 */
	function body()
	{
		return file_get_contents($this->info['tmp_name']);
	}

	/**
	 * Returns the file's body as a readable stream.
	 *
	 * @return resource
	 */
	function stream()
	{
		return fopen($this->info['tmp_name'], "rb");
	}

	/**
	 * Returns the file's declared MIME type.
	 *
	 * @return string
	 */
	function type()
	{
		return $this->info['type'];
	}

	/**
	 * Returns the file's size in bytes.
	 *
	 * @return int
	 */
	function size()
	{
		return $this->info['size'];
	}

	/**
	 * Returns the file's declared name.
	 *
	 * @return string
	 */
	function name()
	{
		return $this->info['name'];
	}

	/**
	 * Saves the uploaded file to the given directory,
	 * automatically generating a filename. Returns the
	 * resulting file path.
	 *
	 * @param string $dir
	 * @return string
	 */
	function saveToDir($dir)
	{
		if (substr($dir, -1) != '/') {
			$dir .= '/';
		}

		if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
			throw new Exception("could not create upload directory '$dir'");
		}

		$path = $dir . $this->newname();
		if (!move_uploaded_file($this->info['tmp_name'], $path)) {
			throw new Exception("could not move uploaded file " . $this->info['tmp_name']);
		}

		return $path;
	}

	/**
	 * Saves the uploaded file to the given path.
	 *
	 * @param string $path
	 */
	function saveTo($path)
	{
		$dir = dirname($path);
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}

		if (!move_uploaded_file($this->info['tmp_name'], $path)) {
			throw new Exception("could not move uploaded file " . $this->info['tmp_name']);
		}

		return $path;
	}

	private function newname()
	{
		$file = $this->info;
		$ext = mime::ext($file['type']);
		if ($ext === null) {
			warning("Unknown uploaded file type: $file[type]");
			$ext = self::ext($file['name']);
		}
		if ($ext == '' && strpos($file['name'], '.') !== false) {
			warning("File '$file[name]' uploaded as octet-stream");
			$ext = self::ext($file['name']);
		}
		if ($ext == '.php') {
			warning(".php file uploaded");
			$ext .= '.txt';
		}
		return uniqid() . $ext;
	}

	/**
	 * Returns maximum upload size in bytes
	 * as defined by current PHP configuration.
	 *
	 * @return int
	 */
	static function maxsize()
	{
		$s1 = self::parse_size(ini_get('post_max_size'));
		$s2 = self::parse_size(ini_get('upload_max_filesize'));
		if ($s1 == -1) return $s2;
		if ($s2 == -1) return $s1;
		return min($s1, $s2);
	}

	private static function parse_size($s)
	{
		if ($s == '-1' || $s === false) return -1;
		preg_match('/^(\d+)(.*?)$/', $s, $m);
		$units = array(
			'K' => 1024,
			'M' => 1024 * 1024,
			'G' => 1024 * 1024 * 1024
		);
		$u = $m[2];
		if (!isset($units[$u])) {
			throw new Exception("Unknown size unit in '$s'");
			return -1;
		}
		return $m[1] * $units[$u];
	}

	private static function ext($filename)
	{
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		if ($ext != '') $ext = '.' . $ext;
		return strtolower($ext);
	}

	private static function noext($filename)
	{
		return pathinfo($filename, PATHINFO_FILENAME);
	}

	private static function errstr($errno)
	{
		switch ($errno) {
			case UPLOAD_ERR_OK:
				return "no error";
			case UPLOAD_ERR_INI_SIZE:
				return "the file exceeds the 'upload_max_filesize' limit";
			case UPLOAD_ERR_FORM_SIZE:
				return "the file exceeds the 'MAX_FILE_SIZE' directive that was specified in the HTML form";
			case UPLOAD_ERR_PARTIAL:
				return "the file was only partially uploaded";
			case UPLOAD_ERR_NO_FILE:
				return "no file was uploaded";
			case UPLOAD_ERR_NO_TMP_DIR:
				return "missing temporary folder";
			case UPLOAD_ERR_CANT_WRITE:
				return "failed to write file to disk";
			default:
				return "unknown error ($errno)";
		}
	}
}
