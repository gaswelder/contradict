<div class="tracklist">
	<table class="tracklist">
		<?php foreach( $album->parts() as $part ): ?>
			<?php if( $album->isSplit()): ?>
				<tr><th colspan="3">{{$part->band()->name}}</th></tr>
			<?php endif; ?>
			<?php foreach ($part->tracks() as $track): ?>
				<tr>
					<td>{{counter('tracknumber')}}</td>
					<td>{{$track->name}} <small>{{$track->comment}}</small></td>
					<td>{{$track->length}}</td>
				</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</table>
	<p>Total length: {{$album->duration()->format()}}</p>
</div>
