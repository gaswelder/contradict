<?php

use havana\dbobject;

class Band extends dbobject
{
	const TABLE_NAME = 'bands';

	public $name;
	public $info;

	static function search($query)
	{
		if (!$query) return [];
		$q = str_replace('%', '%%', $query);
		$ids = db()->getValues('SELECT id FROM bands WHERE name LIKE ? LIMIT 10', "%$q%");
		return static::getMultiple($ids);
	}

	/**
	 * Returns this band's albums.
	 *
	 * @return array
	 */
	function albums()
	{
		$rows = db()->getRows('SELECT * FROM releases
			WHERE id IN (SELECT album_id FROM album_parts WHERE band_id = ?)
			ORDER BY "year"', $this->id);
		return Release::fromRows($rows);
	}
}
