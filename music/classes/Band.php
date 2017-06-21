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
		$ids = db()->getValues('SELECT DISTINCT r.id
			FROM releases r
			JOIN release_tracks rt ON rt.release_id = r.id
			JOIN tracks t ON t.id = rt.track_id
			WHERE t.band_id = ?
			ORDER BY r."year", r.zindex', $this->id);
		return Release::getMultiple($ids);
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
