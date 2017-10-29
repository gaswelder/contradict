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

	function toJSON()
	{
		$lineup = [];
		foreach ($this->performers as $p) {
			foreach ($p->roles as $role) {
				$lineup[$p->person()->name][] = $role;
			}
		}
		return $lineup;
	}
}
