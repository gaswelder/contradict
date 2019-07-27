<?php

class Question
{
	public $reverse;
	private $e;

	function entry()
	{
		return $this->e;
	}

	function __construct(Entry $e, $reverse)
	{
		$this->reverse = $reverse;
		$this->e = $e;
	}

	private function id()
	{
		return $this->e->id;
	}

	private function q()
	{
		return $this->reverse ? $this->e->a : $this->e->q;
	}

	function a()
	{
		return $this->reverse ? $this->e->q : $this->e->a;
	}

	private function times()
	{
		return $this->reverse ? $this->e->answers2 : $this->e->answers1;
	}

	function format()
	{
		return [
			'id' => $this->id(),
			'q' => $this->q(),
			'a' => $this->a(),
			'times' => $this->times(),
			'wikiURL' => $this->wikiURL(),
			'dir' => $this->reverse ? 1 : 0
		];
	}

	function wikiURL()
	{
		$words = explode(' ', $this->e->q);
		if (empty($words)) return null;
		if (in_array(strtolower($words[0]), ['das', 'die', 'der'])) {
			array_shift($words);
		}
		if (count($words) != 1) return null;

		$wiki = $words[0];
		return 'https://de.wiktionary.org/w/index.php?search=' . urlencode($wiki);
	}
}
