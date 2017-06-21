<?= tpl( "top" ) ?>


<h1>{{$band->name}}</h1>

<?= tpl('parts/albums-list', ['albums' => $band->albums()]) ?>

<h2>Lineups</h2>

<?php foreach($band->lineups() as $lineup): ?>
	<?= tpl('parts/lineup', compact('lineup')) ?>
<?php endforeach; ?>

<pre>{{$band->info}}</pre>

<?= tpl( "bottom" ) ?>
