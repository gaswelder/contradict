<?php

use havana\dbobject;

class AlbumPart extends dbobject
{
	const TABLE_NAME = 'album_parts';

	public $album_id;
	public $band_id;
	public $num;

	function band()
	{
		return Band::get($this->band_id);
	}

	function tracks()
	{
		return Track::find(['part_id' => $this->id], 'num');
	}

	function delete()
	{
		db()->exec('DELETE FROM tracks WHERE part_id = ?', $this->id);
		db()->exec('DELETE FROM album_parts WHERE id = ?', $this->id);
	}

	function toJSON()
	{
		$list = [];
		foreach ($this->tracks() as $i) {
			$list[] = [
				'name' => $i->name,
				'length' => $i->length
			];
		}

		return [
			'band' => $this->band()->name,
			'tracks' => $list
		];
	}
}
