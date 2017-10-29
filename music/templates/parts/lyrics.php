<?php foreach ($album->parts() as $part): ?>
	<?php foreach ($part->tracks as $track): ?>
		<?php if (!$track->lyrics) continue; ?>
		<article>
			<h3>{{$track->name}}</h3>
			<pre>{{$track->lyrics}}</pre>
		</article>
	<?php endforeach; ?>
<?php endforeach; ?>
