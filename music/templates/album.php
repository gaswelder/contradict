<?= tpl('top') ?>

<div id="albumInfo">
	<h1>{{$album->name}}</h1>

	<p>by <?php foreach( $album->bands() as $i => $band ): ?>
		{{$i ? ',' : ''}}
		<a href="/music/bands/{{$band->id}}">{{$band->name}}</a>
	<?php endforeach; ?></p>

	<p>{{$album->year}}, {{$album->label}}</p>

	<div class="quick-details">
		<dl>
			<dt>Studio</dt>
			<dd>{{$album->studio}}</dd>
			<dt>Producer</dt>
			<dd>{{$album->producer}}</dd>
			<dt>Artist</dt>
			<dd>{{$album->artworker}}</dd>
		</dl>
	</div>

	<div class="cover">
		<img src="{{$album->coverpath()}}" alt="Front cover">
	</div>

	<?= tpl('parts/tracklist', compact('album')) ?>
	<?= tpl('parts/lineup', ['lineup' => $album->lineup()]) ?>

	<?php
	var_dump($album->studios());
	?>

	<div>{{$album->info}}</div>

	<?= tpl('parts/lyrics', compact('album')) ?>

</div>

<?= tpl('bottom') ?>
