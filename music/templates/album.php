<?= tpl('top') ?>

<nav>
	<a href="/albums/{{$album->id}}/json">JSON</a>
</nav>

<div id="albumInfo">
	<h1>{{$album->name}}</h1>

	<p>by <?php foreach( $album->bands() as $i => $band ): ?>
		{{$i ? ',' : ''}}
		<a href="/bands/{{$band->id}}">{{$band->name}}</a>
	<?php endforeach; ?></p>

	<p>{{$album->year}}, {{$album->label}}</p>



	<div class="quick-details">
		<dl>
			<dt>Studio</dt>
			<?php foreach($album->studios() as $studio): ?>
				<dd>{{$studio->name}} - {{implode(', ', $studio->roles)}}</dd>
			<?php endforeach; ?>
		</dl>
	</div>

	<div class="cover">
		<img src="{{$album->coverpath()}}" alt="Front cover">
	</div>

	<?= tpl('parts/tracklist', compact('album')) ?>
	<?= tpl('parts/lineup', ['lineup' => $album->lineup()]) ?>

	<div>{{$album->info}}</div>

	<?= tpl('parts/lyrics', compact('album')) ?>

</div>

<?= tpl('bottom') ?>
