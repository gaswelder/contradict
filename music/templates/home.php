<?= tpl( "top" ); ?>

<div id="mainPageRandomReleases">
	<h1>Random releases for today</h1>

	<?= tpl('parts/albums-list', compact('albums')) ?>
</div>

<?= tpl( "bottom" ); ?>
