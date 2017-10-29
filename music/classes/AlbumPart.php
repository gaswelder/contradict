<?php

class AlbumPart
{
	public $tracks = [];
	public $band;

	function toJSON()
	{
		$list = [];
		foreach ($this->tracks as $i) {
			$list[] = [
				'name' => $i->name,
				'length' => $i->length
			];
		}

		return [
			'band' => $this->band->name,
			'tracks' => $list
		];
	}
}
