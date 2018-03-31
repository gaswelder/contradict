<?php

class Question
{
	private $reverse;
	private $e;

	function __construct(Entry $e, $reverse)
	{
		$this->reverse = $reverse;
		$this->e = $e;
	}

	function id()
	{
		return $this->e->id;
	}

	function q()
	{
		return $this->reverse ? $this->e->a : $this->e->q;
	}

	function a()
	{
		return $this->reverse ? $this->e->q : $this->e->a;
	}

	function times()
	{
		return $this->reverse ? $this->e->answers2 : $this->e->answers1;
	}

	function hint()
	{
		$sim = $this->similars();
		if ($sim->len() == 0) return null;
		$field = $this->reverse ? 'q' : 'a';
		$hint = self::h($this->e->$field, $sim->pluck($field)->get());
		return preg_replace('/\*+/', '...', $hint);
	}

	function checkAnswer($answer)
	{
		list($answerField, $counterField) = $this->reverse ? ['q', 'answers2'] : ['a', 'answers1'];
		if (mb_strtolower($this->e->$answerField) == mb_strtolower($answer)) {
			$this->e->$counterField++;
			return true;
		}
		return false;
	}

	function save()
	{
		$this->e->save();
	}

	private function similars()
	{
		$filter = $this->reverse ? ['a' => $this->e->a] : ['q' => $this->e->q];
		return Arr::make(Entry::find($filter))->filter(function (Entry $e) {
			return $e->id != $this->e->id;
		});
	}

	static function h($word, $others)
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
		return $replace . self::h($rest($word), array_map($rest, $others));
	}
}
