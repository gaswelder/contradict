<?php

use havana\dbobject;

/**
 * Represents a single track from an album.
 */
class Track extends dbobject
{
	const TABLE_NAME = 'tracks';

	public $band_id;
	public $name;
	public $comment;
	public $length;
	public $lyrics;

	public function performers()
	{
		$performers = db()->getRecords("select * from track_performers where track_id = ?", $this->id);
		return Performer::fromRows($performers);
	}

	/**
	 * Returns studios associated with this track.
	 *
	 * @return array
	 */
	public function studios()
	{
		$studios = db()->getRecords('select * from track_studios where track_id = ?', $this->id);
		return TrackStudio::fromRows($studios);
	}
}
