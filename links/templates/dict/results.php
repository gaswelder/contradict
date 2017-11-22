<?= tpl('dict/header') ?>

<nav>
	<a href="/dict/test">New test</a>
	<a href="/dict">Home</a>
</nav>
<style>
.nope td:nth-child(2) {
	color: red;
	text-decoration: line-through;
}
</style>
<table>
<tr>
	<th>Q</th>
	<th>A</th>
	<th>Expected</th>
	<th></th>
</tr>
<?php foreach ($results as $r): ?>
	<tr class="{{$r['ok'] ? 'ok' : 'nope'}}">
		<td>{{$r['q']}}</td>
		<td>{{$r['a']}}</td>
		<td>{{$r['expected']}}</td>
		<td>{{$r['ok'] ? 'ok' : 'nope'}}</td>
	</tr>
<?php endforeach; ?>
</table>