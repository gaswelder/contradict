<?php

use havana\dbobject;

class Link extends dbobject
{
    const TABLE_NAME = 'links';

    public $created_at;
    public $updated_at;
    public $category = '';
	public $url;
	public $archive = 0;

    function __construct()
    {
        $this->created_at = time();
        $this->updated_at = time();
	}

	static function categories()
	{
		return db()->getValues("SELECT DISTINCT category FROM links WHERE category <> ''");
	}

	static function all()
	{
		return self::find([]);
	}

	static function active()
	{
		return self::find(['archive' => 0], 'updated_at');
	}

	static function fromCategory($cat)
	{
		return self::find(['archive' => 0, 'category' => $cat]);
	}
}
