<?php

use havana\dbobject;

/**
 * Represents an album.
 */
class Release extends dbobject
{
	const TABLE_NAME = 'releases';

	public $coverpath = '';
	public $name;
	public $year;
	public $label;
	public $info;

	public $producer = '';
	public $artworker = '';

	static function todays()
	{
		$date = date( "Y-m-d" );
		$data = files::get( "main_page_releases" );
		if( $data ) $data = unserialize( $data );
		if( !$data || $data['date'] != $date ) {
			$ids = db()->getValues('select id from releases order by rand() limit 9');
			$data = array( 'date' => $date, 'ids' => $ids );
			files::save( "main_page_releases", serialize( $data ) );
		}
		return Release::getMultiple($data['ids']);
	}

	function coverpath()
	{
		return '/' . $this->coverpath;
	}

	/**
	 * Returns album sections.
	 *
	 * @return array
	 */
	 function parts()
	 {
		 return AlbumPart::find(['album_id' => $this->id], 'num');
	 }

	/**
	 * Returns bands associated with this album.
	 *
	 * @return array
	 */
	function bands()
	{
		$rows = db()->getRows('SELECT * FROM bands
			WHERE id IN (SELECT band_id FROM album_parts WHERE album_id = ?)', $this->id);
		return Band::fromRows($rows);
	}

	/**
	 * Returns true if this album is a split.
	 *
	 * @return bool
	 */
	function isSplit()
	{
		$bands = array_map(function(AlbumPart $part) {
			return $part->band_id;
		}, $this->parts());
		return count(array_unique($bands)) > 1;
	}

	/**
	 * Returns total album length.
	 *
	 * @return Duration
	 */
	function duration()
	{
		$sum = 0;
		foreach ($this->parts() as $part) {
			foreach ($part->tracks() as $track) {
				$sum += fmt::parseDuration($track->length);
			}
		}
		return new Duration($sum);
	}

	function toJSON()
	{
		return [
			'name' => $this->name,
			'year' => $this->year,
			'label' => $this->label,
			'parts' => array_map(function(AlbumPart $p) {
				return $p->toJSON();
			}, $this->parts())
		];
	}

	function saveData($data)
	{
		db()->exec('start transaction');

		if ($this->id) {
			foreach ($this->parts() as $part) {
				$part->delete();
			}
		}

		$this->name = $data['name'];
		$this->year = $data['year'];
		$this->label = $data['label'] ?? '';
		$this->save();
	
		foreach ($data['parts'] as $partIndex => $partData) {
	
			$bands = Band::find(['name' => $partData['band']]);
			if (count($bands) > 1) {
				panic("Ambiguous band name: $partData[band]");
			}
			if (count($bands) == 1) {
				$band = $bands[0];
			} else {
				$band = new Band();
				$band->name = $partData['band'];
				$band->save();
			}
	
			// $lineup = [];
			// foreach ($part['lineup'] as $name => $roles) {
			// 	$people = Person::find(['name' => $name]);
			// 	if (count($people) > 1) {
			// 		panic("Ambiguous person name: $name");
			// 	}
			// 	if (count($people) == 0) {
			// 		$person = new Person();
			// 		$person->name = $name;
			// 		$person->save();
			// 	} else {
			// 		$person = $people[0];
			// 	}
			// 	$lineup[] = [$person, $roles];
			// }
	
			$part = new AlbumPart;
			$part->album_id = $this->id;
			$part->band_id = $band->id;
			$part->num = $partIndex;
			$part->save();
	
			foreach ($partData['tracks'] as $i => $data) {
				$track = new Track;
				$track->part_id = $part->id;
				$track->length = $data['length'];
				$track->name = $data['name'];
				$track->num = $i + 1;
				$track->save();
			}
		}
		db()->exec('commit');
	}
}
