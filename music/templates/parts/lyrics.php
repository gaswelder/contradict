<?php
foreach ($album->tracks() as $i => $track) {
	if (!$track->lyrics) {
		continue;
	}
	?>
	<article>
		<h3>{{$i + 1}}. {{$track->name}}</h3>
		<pre>{{$track->lyrics}}</pre>
	</article>
	<?php
}
