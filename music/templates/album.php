<?= tpl('top') ?>

<nav>
	<a href="/albums/{{$album->id}}/json">JSON</a>
	<a href="/albums/{{$album->id}}/newcover">Upload a cover</a>
</nav>

<div id="albumInfo">
	<h1>{{$album->name}}</h1>

	<p>by <?php foreach( $album->bands() as $i => $band ): ?>
		{{$i ? ',' : ''}}
		<a href="/bands/{{$band->id}}">{{$band->name}}</a>
	<?php endforeach; ?></p>

	<p>{{$album->year}}, {{$album->label}}</p>

	<div class="cover">
		<img src="{{$album->coverpath()}}" alt="Front cover">
	</div>

	<?= tpl('parts/tracklist', compact('album')) ?>

	<div>{{$album->info}}</div>

	<?= tpl('parts/lyrics', compact('album')) ?>

</div>

<?= tpl('bottom') ?>
