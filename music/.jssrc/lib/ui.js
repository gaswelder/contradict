var ui = {};

ui.contextMenu = function()
{
	var $m = $( '<div class="w context-menu"></div>' );
	$m.css({
		"position": "absolute"
	});
	
	var items = [];

	this.add = function( name, func )
	{
		var $e = $( '<div data-id="'+items.length+'">' + name + '</div>' );
		$m.append( $e );
		items.push( [name, func] );
	};
	
	this.show = function( x, y )
	{
		$m.css({
			"left": x + "px",
			"top": y + "px"
		});
		$( 'body' ).append( $m );
	};
	
	$m.on( "click", "> div", function() {
		var i = $( this ).data( "id" );
		items[i][1].call( this );
	});

	$( 'body' ).on( "click", function() {
		$m.detach();
	});
};
