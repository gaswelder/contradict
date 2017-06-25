<?= tpl('edit/top') ?>

<h1>{{$band->name}}</h1>

<?php foreach($band->albums() as $album): ?>
	<a href="/music/edit/albums/{{$album->id}}">{{$album->name}} ({{$album->year}})</a>
<?php endforeach; ?>

<?= tpl('edit/bottom') ?>
