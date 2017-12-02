<?= tpl('dict/header') ?>

<nav>
	<a href="/dict/test">New test</a>
	<a href="/dict">Home</a>
</nav>
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
		<td>{{$r['q']}}</td>
		<td>{{$r['expected']}}</td>
		<td>{{$r['a']}}</td>
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
		<td>{{$r['q']}}</td>
		<td>{{$r['expected']}}</td>
		<td>ok</td>
	</tr>
<?php endforeach; ?>
</table>
