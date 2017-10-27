<?php

use havana\dbobject;

/**
 * Represents an album.
 */
class Release extends dbobject
{
	const TABLE_NAME = 'releases';

	public $coverpath;
	public $name;
	public $year;
	public $label;
	public $info;

	public $producer;
	public $artworker;
	

	/**
	 * Returns $count random albums.
	 *
	 * @param int $count
	 * @return array
	 */
	static function getRandom($count)
	{
		$ids = db()->getValues('select id from releases order by rand() limit 0,'.intval($count));
		return self::getMultiple($ids);
	}

	public function coverpath()
	{
		return '/' . $this->coverpath;
	}

	/**
	 * Returns bands associated with this album.
	 *
	 * @return array
	 */
	public function bands()
	{
		$rows = db()->getRows('SELECT * FROM bands
			WHERE id IN (SELECT band_id FROM tracks WHERE album_id = ?)', $this->id);
		return Band::fromRows($rows);
	}

	/**
	 * Returns tracks from this album.
	 *
	 * @return array
	 */
 	public function tracks()
	{
		$rows = db()->getRows('SELECT * FROM tracks WHERE album_id = ? ORDER BY num', $this->id);
		return Track::fromRows($rows);
	}

	/**
	 * Returns this album's lineup.
	 *
	 * @return Lineup
	 */
	public function lineup()
	{
		$rows = db()->getRows(
			'SELECT
				t.id AS track_id, p.person_id, p.role, p.stagename, p.guest
			FROM tracks t JOIN track_performers p ON p.track_id = t.id
			WHERE t.band_id = ?', $this->id);

		$fields = ['track_id', 'role', 'stagename', 'guest'];
		// Group by person
		$perfs = [];
		foreach($rows as $row) {
			$id = $row['person_id'];
			if(!isset($perfs[$id])) {
				$perfs[$id] = ['id' => $id];
			}
			foreach($fields as $k) {
				$perfs[$id][$k][] = $row[$k];
			}
		}

		foreach($perfs as $id => $perf) {
			foreach($fields as $k) {
				$perfs[$id][$k] = array_unique($perf[$k]);
			}
		}

		$lineup = new Lineup();
		foreach($perfs as $id => $perf) {
			$p = new Performer();
			$p->person_id = $id;
			$p->roles = array_unique($perf['role']);
			$p->track_ids = array_unique($perf['track_id']);
			$p->stagenames = array_unique($perf['stagename']);
			$p->guest = array_unique($perf['guest']);
			$lineup->performers[] = $p;
		}

		return $lineup;
	}

	/**
	 * Returns studios associated with this album, as AlbumStudio objects.
	 *
	 * @return array
	 */
	public function studios()
	{
		$studios = [];

		$rows = db()->getRows('SELECT track_id, studio_id, role
			FROM track_studios
			WHERE track_id IN (
				SELECT id FROM tracks WHERE album_id = ?)', $this->id);

		foreach($rows as $row) {
			$id = $row['studio_id'];
			if(!isset($studios[$id])) {
				$st = Studio::get($id);
				$s = new AlbumStudio();
				$s->id = $st->id;
				$s->name = $st->name;
				$studios[$id] = $s;
			}
			$s = $studios[$id];
			$s->push('roles', $row['role']);
			$studios[$id]->track_ids[] = $row['track_id'];
		}

		return array_values($studios);
	}

	/**
	 * Returns true if this album is a split.
	 *
	 * @return bool
	 */
	public function isSplit()
	{
		$ids = array_map(function($track) {
			return $track->band_id;
		}, $this->tracks());
		return count( array_unique($ids) ) > 1;
	}

	/**
	 * Returns total album length in seconds.
	 *
	 * @return int
	 */
	public function totalLength()
	{
		$sum = 0;
		foreach($this->tracks() as $track) {
			$sum += fmt::parseDuration($track->length);
		}
		return $sum;
	}
}
