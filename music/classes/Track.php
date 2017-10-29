<?php

use havana\dbobject;

/**
 * Represents a single track from an album.
 */
class Track extends dbobject
{
	const TABLE_NAME = 'tracks';

	public $band_id;
	public $album_id;
	public $name;
	public $num;
	public $comment = '';
	public $length;
	public $lyrics = '';

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
		return Band::get($this->band_id);
	}
}
