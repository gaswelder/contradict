<?php

class Band extends dbobject
{
	const TABLE_NAME = 'bands';

	static function search($query)
	{
		if (!$query) return [];
		$q = str_replace('%', '%%', $query);
		$ids = db()->getValues('SELECT id FROM bands WHERE name LIKE ? LIMIT 10', "%$q%");
		return static::getMultiple($ids);
	}

	/**
	 * Returns band's albums
	 *
	 * @return array
	 */
	public function albums()
	{
		$rows = db()->getRecords('SELECT * FROM releases
			WHERE id IN (SELECT album_id FROM tracks WHERE band_id = ?)
			ORDER BY "year"', $this->id);
		return Release::fromRows($rows);
	}

	/**
	 * Returns band's lineups.
	 *
	 * @return array
	 */
	public function lineups()
	{
		$lineups = [];
		foreach ($this->albums() as $album) {
			$l = $album->lineup();
			$h = $l->hash();
			$lineups[$h] = $l;
		}
		return array_values($lineups);
	}
}
