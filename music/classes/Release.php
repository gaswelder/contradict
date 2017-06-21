<?php

/**
 * Represents an album.
 */
class Release extends dbobject
{
	const TABLE_NAME = 'releases';

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
		return '/music/' . $this->coverpath;
	}

	/**
	 * Returns bands associated with this album.
	 *
	 * @return array
	 */
	public function bands()
	{
		$bandIds = db()->getValues("
			SELECT DISTINCT b.id, b.name
			FROM bands b
			JOIN tracks t ON t.band_id = b.id
			JOIN release_tracks rt ON rt.track_id = t.id
			WHERE rt.release_id = ?", $this->id);
		return Band::getMultiple($bandIds);
	}

	/**
	 * Returns tracks from this album.
	 *
	 * @return array
	 */
 	public function tracks()
	{
		$ids = db()->getValues('select track_id from release_tracks where release_id = ? order by num', $this->id);
		return Track::getMultiple($ids);
	}

	/**
	 * Returns this album's lineup.
	 *
	 * @return Lineup
	 */
	public function lineup()
	{
		$rows = db()->getRecords(
			'SELECT
				rt.track_id, p.person_id, p.role, p.stagename, p.guest
			FROM releases r
				JOIN release_tracks rt ON rt.release_id = r.id
				JOIN track_performers p ON p.track_id = rt.track_id
			WHERE r.id = ?', $this->id);

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
	 * Returns studios associated with this album.
	 *
	 * @return array
	 */
	public function studios()
	{
		$studios = [];
		foreach($this->tracks() as $track) {
			$studios = array_merge($studios, $track->studios());
		}
		return $studios;
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
