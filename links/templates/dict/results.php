<?= tpl('dict/header') ?>

<style>
.nope td:nth-child(3) {
	color: red;
	text-decoration: line-through;
}
</style>
<table>
<tr>
	<th>Q</th>
	<th>Expected</th>
	<th>A</th>
</tr>
<?php foreach ($fail as $r): ?>
	<tr class="nope">
		<td>{{$r->question()}}</td>
		<td>
			<?php foreach ($r->entries() as $i => $entry): ?>
				<?php if ($i > 0): ?>||<?php endif; ?>
				<?php
					$exp = $entry->expected($r->dir());
					$wiki = array_reduce(explode(' ', $exp), function($prev, $next) {
						if (mb_strlen($next) > mb_strlen($prev)) return $next;
						return $prev;
					}, '');
				?>
				<a href="/dict/entries/{{$entry->id}}">{{ $exp }}</a>
				(<small><a href="{{ 'https://de.wiktionary.org/w/index.php?search='.urlencode($wiki).'&title=Spezial%3ASuche&go=Seite' }}">wiki</a></small>)
			<?php endforeach; ?>
		</td>
		<td>{{$r->answer()}}</td>
	</tr>
<?php endforeach; ?>
</table>

<table>
<tr>
	<th>Q</th>
	<th>A</th>
	<th></th>
</tr>
<?php foreach ($ok as $r): ?>
	<tr>
	<td>{{$r->question()}}</td>
		<td>{{$r->match()->expected($r->dir())}}</td>
		<td>ok</td>
	</tr>
<?php endforeach; ?>
</table>

<nav>
	<a class="btn" href="/dict/test">New test</a>
	<a class="btn" href="/dict">Home</a>
</nav>
