<div class="albums-list">
	<?php foreach($albums as $album): ?>
		<div class="album-icon">
			<a href="/albums/{{$album->id}}">
			    <div class="img-container"><img src="{{$album->coverpath()}}" alt=""></div>
			    <div class="title">{{$album->name}} ({{$album->year}})</div>
			</a>
		</div>
	<?php endforeach; ?>
</div>
