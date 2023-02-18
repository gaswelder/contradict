<?php

class Question
{
	public $reverse;
	private $e;
	private $dict;

	function __construct(Dict $d, Entry $e, $reverse)
	{
		$this->dict = $d;
		$this->reverse = $reverse;
		$this->e = $e;
	}

	function hint()
	{
		$q = $this;
		$entry = $q->e;
		$sim = $q->dict->similars($entry, $q->reverse);
		if (count($sim) == 0) {
			return null;
		}
		$field = $q->reverse ? 'q' : 'a';
		$values = [];
		foreach ($sim as $entry) {
			$values[] = $entry->$field;
		}
		$hint = h($q->e->$field, $values);
		return preg_replace('/\*+/', '...', $hint);
	}

	/**
	 * Returns true if the given answer is correct for this question.
	 */
	function checkAnswer(string $answer): bool
	{
		$realAnswer = $this->reverse ? $this->e->q : $this->e->a;
		return mb_strtolower($realAnswer) == mb_strtolower($answer);
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
			'urls' => $this->dict->wikiURLs($this->e->q),
			'dir' => $this->reverse ? 1 : 0
		];
	}
}

function h($word, $others)
{
	$list = array_unique(array_merge([$word], $others));
	if (count($list) < 2) return null;

	$first = array_map(function ($str) {
		return mb_substr($str, 0, 1);
	}, $list);

	if (count(array_unique($first)) == count($first)) {
		return $first[0] . (mb_strlen($word) > 1 ? '*' : '');
	}
	$rest = function ($str) {
		return mb_substr($str, 1);
	};
	$replace = $first[0] == ' ' ? ' ' : '*';
	return $replace . h($rest($word), array_map($rest, $others));
}
