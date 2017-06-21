<div class="comment">
	<div class="head">
		<span class="author">{{$comment->author_id}}</span>, {{$comment->time_added}}
	</div>
	<?= markdown_output( $comment->text ) ?>

	<?php if( !empty( $files ) ): ?>
	<div class="attachments">
		<?php
		foreach( $files as $path )
		{
			$ext = ext( $path );
			if( $ext == '.jpg' || $ext == '.png' ) {
				$s = html::img( image_url( $path, 200 ) );
			}
			else {
				$s = basename( $path );
			}
			printf( '<a href="%s">%s</a>', '/'.$path, $s );
		}
		?>
	</div>
	<?php endif; ?>
</div>
