<?php

class Page
{
	public $name;
	public $content;

	function __construct($name)
	{
		$this->name = $name;

		$path = $this->path();
		if (file_exists($path)) {
			$this->content = file_get_contents($path);
		}
	}

	function save()
	{
		$path = $this->path();
		if (file_exists($path)) {
			$dir = dirname($path) . '/.backup';
			if (!file_exists($dir)) mkdir($dir);
			copy($path, $dir.'/'.date('Y-m-d-H-i-s').'_'.$this->name);
		}
		file_put_contents($path, $this->content);
	}

	private function path()
	{
		$dir = __DIR__.'/../data';
		$path = $dir.'/'.$this->name;
		return $path;

	}

	static function ls()
	{
		$dir = __DIR__.'/../data';
		return array_map('basename', glob($dir .'/*'));
	}
}
