<?php

class Lineup
{
	public $performers = [];

	/**
	 * Returns string unique for this particular lineup.
	 *
	 * @return string
	 */
	public function hash()
	{
		$ids = [];
		foreach($this->performers as $p) {
			$ids[] = $p->person_id;
		}
		sort($ids);
		return implode(',', $ids);
	}
}
