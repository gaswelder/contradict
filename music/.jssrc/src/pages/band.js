pageMain( "band", function()
{
	initReleases();
});

function initReleases()
{
	var menu = new ui.contextMenu();
	menu.add( "Add release", function()
	{
		showDialog();
	});

	$releases.on( "contextmenu", function( event )
	{
		event.preventDefault();
		menu.show( event.pageX, event.pageY );
	});


	function showDialog()
	{
		var $c = $( '<div>'
			+ '<div><label>Name</label><input name="name"></div>'
			+ '<div><label>Year</label><input name="year"></div>'
			+ '</div>' );
		var d = new Dialog( $c.get(0) );
		d.setTitle( "New release" );
		d.addButton( "Save", function() {

		});
		d.addButton( "Cancel", null, "no" );
		d.show();
	}
}
