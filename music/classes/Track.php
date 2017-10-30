<?php

use havana\dbobject;

/**
 * Represents a single track from an album.
 */
class Track extends dbobject
{
	const TABLE_NAME = 'tracks';

	//public $album_id;
	public $name;
	public $num;
	public $comment = '';
	public $length;
	public $lyrics = '';

	public $part_id;

	/**
	 * Returns studios associated with this track.
	 *
	 * @return array
	 */
	function studios()
	{
		return TrackStudio::find(['track_id' => $this->id]);
	}

	/**
	 * Returns band to which this track belongs.
	 *
	 * @return Band
	 */
	function band()
	{
		oops();
		return Band::get($this->band_id);
	}
}
