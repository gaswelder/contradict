var mdb = new mdb();
function mdb()
{
	var dx = new DX( "/music/dx" );
}

function mdb_album( id )
{
	var dx = new DX( "/music/dx" );

	this.id = function() {
		return id;
	};

	this.get = function( field, value ) {
		return dx.get( "albums/" + id + "/" + field );
	};

	this.set = function( field, value ) {
		var data = new FormData();
		data.append( "value", value );
		return dx.postForm( "albums/" + id + "/" + field, data );
	};
}
