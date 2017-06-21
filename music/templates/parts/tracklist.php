<div class="tracklist">
	<table class="tracklist">
	<?php

	$band_id = 0;

	foreach( $album->tracks() as $i => $track )
	{
		if( $album->isSplit() && $track->band_id != $band_id ) {
			$band_id = $track->band_id;
			?>
			<tr><th colspan="3">{{$band->name}}</th></tr>
			<?php
		}
		?>
		<tr>
			<td>{{$i + 1}}</td>
			<td>{{$track->name}} <small>{{$track->comment}}</small></td>
			<td>{{$track->length}}</td>
		</tr>
		<?php
	}
	?>
	</table>
	<p>Total length: {{formatDuration($album->totalLength())}}</p>
</div>
