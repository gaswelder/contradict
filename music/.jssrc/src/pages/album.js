pageMain( "album", function()
{
	var $c = $( "#albumInfo" );
	var albumId = $c.data( "id" );
	var album = new mdb_album( albumId );

	var menu = new ui.contextMenu();
	$c.on( "contextmenu", function( event ) {
		event.preventDefault();
		menu.show( event.pageX, event.pageY );
	});

	initTracklist( album, menu );
	init( 'info' );
	init( 'lineup' );
	init( 'studios' );
	init( 'lyrics' );
	initCover( album, menu );

	function init( field )
	{
		var $cont = $( "#albumInfo ." + field );
		var title = "Edit " + field;
		menu.add( title, function() {
			album.get( field ).then( showDialog ).catch( showError );
		});

		function showDialog( info ) {
			showEditor( title, info, function( content ) {
				return album.set( field, content ).then( reloadDesc );
			});
		}

		function reloadDesc() {
			var url = "/music/albums/" + album.id() + "/" + field + ".htm";
			updateContainer( $cont, http.get( url ) );
		}
	}
});

function initCover( album, menu )
{
	menu.add( "Change cover", function()
	{
		var $c = $( '<div></div>' );
		var $preview = $( '<div class="image-preview"></div>' );
		var $form = $( '<form><input type="file" accept="image/*"></form>' );
		$c.append( $preview );
		$c.append( $form );
		var $input = $form.find( "input" );

		var d = new Dialog( $c );
		d.addButton( "Upload", function()
		{
			var F = $input.get(0).files;
			if( F.length == 0 ) {
				alert( "No files to upload" );
				return;
			}

			album.set( "cover", F[0] )
			.then( function( v )
			{
				var $img = $( "#albumInfo .cover img" );
				var url = $img.attr( 'src' );
				var rand = Math.round( Math.random() * 1000 );
				if( url.indexOf( '?' ) != -1 ) {
					url += '&rand=' + rand;
				}
				else {
					url += '?rand=' + rand;
				}
				$img.attr( 'src', url );
				d.close();
			})
			.catch( showError );
		});

		d.addButton( "Cancel", null, "no" );
		d.show();

		$input.on( "change", function() {
			$preview.html( "" );
			if( this.files.length == 0 ) {
				return;
			}
			var f = this.files[0];
			var r = new FileReader();
			r.onload = function() {
				$preview.html( '<img src="'+r.result+'" alt="Preview">' );
			};
			r.readAsDataURL( f );
		});
	});
}




function initTracklist( album, menu )
{
	var $tracklist = $( "div.tracklist" );

	menu.add( "Edit track list", function()
	{
		album.get( "tracklist" ).then( function( src )
		{
			showEditor( "Edit track list", src, function( newSrc ) {
				return album.set( "tracklist", newSrc ).then( reload );
			});
		}).catch( showError );
	});

	function reload()
	{
		var url = "/music/albums/" + album.id() + "/tracklist.htm";
		updateContainer( $tracklist, http.get( url ) );
	}
}

function updateContainer( $cont, promise )
{
	$cont.addClass( "reloading" );
	promise.then( function( html ) {
		$cont.html( html );
		$cont.removeClass( "reloading" );
	});
}

function showEditor( title, content, onSave )
{
	var $c = $( '<div><textarea></textarea></div>' );
	var $t = $c.find( "textarea" );
	$t.val( content );

	var d = new Dialog( $c.get(0) );
	d.setTitle( title );
	d.addButton( "Save", function() {
		onSave( $t.val() ).then( d.close ).catch( showError );
	} );
	d.addButton( "Cancel", null, "no" );
	d.show();
}

function showError( e ) {
	console.error( e );
	alert( "Error: " + e );
}
