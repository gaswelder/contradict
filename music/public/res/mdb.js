/*
	Compilation date: 2016-05-18
	Number of files: 10
*/
(function() {
"use strict";

// lib/args.js
function scriptArgs()
{
	/*
	 * Get the URL from the last loaded script element.
	 */
	var S = document.getElementsByTagName( 'script' );
	var url = S[S.length-1].getAttribute( 'src' );

	/*
	 * Parse the query string and return it.
	 */
	var args = {};
	var pos = url.indexOf( '?' );
	if( pos >= 0 )
	{
		var parts = url.substr( pos + 1 ).split( '&' );
		for( var i = 0; i < parts.length; i++ )
		{
			var kv = parts[i].split( '=' );
			args[kv[0]] = kv[1];
		}
	}
	return args;
}


// lib/dialog.js
/*
 * A wrapper around Layers to create dialogs with content and buttons.
 */
function Dialog( content )
{
	/*
	 * A Layers object.
	 */
	var layer = null;
	/*
	 * Keydown listeners.
	 */
	var windowListeners = [];

	var $container = $( '<div class="w dialog"></div>' );
	var $title = $( '<div class="title"></div>' );
	var $content = $( '<div class="content"></div>' );
	if( content ) {
		$content.append( content );
	}
	var $buttons = $( '<div class="buttons"></div>' );
	var $yesButton = null;
	var $noButton = null;

	/*
	 * The 'onclick' is a function to be called when the button is
	 * pressed. If not specified, then "close" function will be
	 * assigned.
	 *
	 * The 'keytype' may be set to "yes" or "no" to give the button
	 * a meaning which will be used when processing key presses.
	 */
	this.addButton = function( title, onclick, keytype )
	{
		var $b = $( '<button type="button">' + title + '</button>' );
		/*
		 * If no function, use 'close', and treat it as a 'yes' button,
		 * unless specified otherwise.
		 */
		if( !onclick ) {
			onclick = this.close.bind( this );
			if( !keytype ) keytype = 'yes';
		}

		$b.on( 'click', onclick.bind( this ) );
		$buttons.append( $b );

		switch( keytype ) {
			case 'yes':
				$yesButton = $b;
				break;
			case 'no':
				$noButton = $b;
				break;
		}
	};

	this.setTitle = function( title ) {
		$title.html( title );
	};

	this.show = function()
	{
		/*
		 * If there are no buttons, add a default one.
		 */
		if( !$buttons.is( ':parent' ) ) {
			this.addButton( 'OK' );
		}
		$container.append( $title ).append( $content ).append( $buttons );
		layer = Layers.create( $container.get(0) );
		/*
		 * If positive button was defined, focus on it.
		 */
		if( $yesButton ) {
			$yesButton.focus();
		}

		if( $yesButton ) listenKeys( this, 13, $yesButton ); // enter
		if( $noButton ) listenKeys( this, 27, $noButton ); // escape

		layer.onBlur( function() {
			callListeners( 'blur' );
		});
		layer.onFocus( function() {
			callListeners( 'focus' );
		});
	};

	function listenKeys( _this, code, $b )
	{
		var f = function( event ) {
			if( event.keyCode != code ) {
				return;
			}
			if( !layer.hasFocus() ) {
				return;
			}
			$b.click();
			event.stopPropagation();
		};
		windowListeners.push( f );
		$(window).on( 'keydown', f );
	}

	this.close = function()
	{
		//$container.remove();
		//$container = null;
		layer.remove();
		layer = null;
		while( windowListeners.length > 0 ) {
			$(window).off( 'keydown', windowListeners.pop() );
		}
	};

	this.isOpen = function() {
		return layer != null;
	};

	this.focus = function() {
		layer.focus();
	};

	var listeners = {
		"focus": [],
		"blur": []
	};

	function callListeners( type ) {
		for( var i = 0; i < listeners[type].length; i++ ) {
			listeners[type][i]();
		}
	}

	this.on = function( type, func ) {
		if( !(type in listeners) ) {
			throw "Unknown event type: " + type;
		}
		listeners[type].push( func );
	};
}

Dialog.show = function( msg ) {
	(new Dialog( msg )).show();
};


// lib/dx.js
function DX( baseUrl )
{
	/*
	 * RTT estimation and time of the last request.
	 */
	var rtt = 0;
	var t = 0;

	this.RTT = function() { return rtt; }

	this.get = function( path, args )
	{
		t = Date.now();
		var url = baseUrl + '/' + path;
		return http.get( url, args ).then( check );
	};

	this.post = function( path, data )
	{
		t = Date.now();
		var url = baseUrl + '/' + path;
		return http.post( url, data ).then( check );
	};

	this.postForm = function( path, formData )
	{
		t = Date.now();
		var url = baseUrl + '/' + path;
		return http.postForm( url, formData ).then( check );
	};

	function check( data )
	{
		rtt = Date.now() - t;

		if( typeof data != "object" ) {
			throw "Malformed response";
		}
		if( !("errno" in data) || !("errstr" in data) ) {
			throw "Wrong response format";
		}
		if( data.errno ) {
			throw data.errstr;
		}
		return data.data;
	}
}


// lib/http.js
"use strict";

var http = (function()
{
	var http = {};

	/*
	 * Send a GET request using vars as query arguments.
	 * The URL may have arguments too.
	 */
	http.get = function( url, vars )
	{
		if( vars ) {
			url = createURL( url, vars );
		}
		var req = new XMLHttpRequest();
		var p = promise( req );
		req.open( "GET", url );
		req.send();
		return p;
	};

	/*
	 * Send a POST request using data as post values.
	 */
	http.post = function( url, data )
	{
		var req = new XMLHttpRequest();
		var p = promise( req );
		req.open( "POST", url );
		var body = encodeForm( data );
		req.setRequestHeader( "Content-Type", "application/x-www-form-urlencoded" );
		req.send( body );
		return p;
	};

	http.postForm = function( url, formData )
	{
		if( !formData instanceof FormData ) {
			throw new TypeError( "formData must be a FormData object" );
		}

		var req = new XMLHttpRequest();
		var p = promise( req );
		req.open( "POST", url );
		req.send( formData );
		return p;
	};

	function encodeForm( data )
	{
		var lines = [];
		for( var k in data ) {
			lines.push( k + "=" + encodeURIComponent( data[k] ) );
		}
		return lines.join( "&" );
	}

	/*
	 * Creates urls. "vars" is a dict with query vars. "base" can have
	 * variables in it too.
	 * Example: createURL( '/?v=json&b=mapdata', {p: bounds, lat: ...} )
	 */
	function createURL( base, vars )
	{
		var url = base;
		var haveQ = url.indexOf( '?' ) != -1;

		for( var i in vars )
		{
			if( typeof vars[i] == "undefined" ) continue;

			if( !haveQ ) {
				url += '?';
				haveQ = true;
			} else {
				url += '&';
			}

			url += i + "=" + encodeURIComponent( vars[i] );
		}
		return url;
	};

	/*
	 * Creates a Promise for given XMLHttpRequest object.
	 */
	function promise( req )
	{
		var ph = {};

		var p = new Promise( function( ok, fail ) {
			ph.ok = ok;
			ph.fail = fail;
		});

		var aborted = false;

		p.abort = function() {
			if( aborted ) return;
			aborted = true;
			req.abort();
		};

		req.onreadystatechange = function() {
			/*
			 * If still working, continue.
			 */
			if( req.readyState != 4 ) { // 4 = DONE
				return;
			}

			/*
			 * If the HTTP response status is not OK, fail the promise
			 * with this status.
			 */
			if( req.status != 200 ) { // 200 = HTTP_OK
				ph.fail( req.statusText );
				return;
			}

			if( aborted ) {
				ph.fail( "aborted" );
				return;
			}

			ph.ok( responseValue( req ) );
		};
		return p;
	}

	function responseValue( req )
	{
		var type = req.getResponseHeader( "Content-Type" );
		if( !type ) type = "text/html; charset=UTF-8";

		if( type.indexOf( "application/json" ) == 0 ) {
			return JSON.parse( req.responseText );
		}

		return req.response;
	}

	return http;
})();


// lib/layers.js
var Layers = (function() {
	var Layers = {};

	var CLASS = 'w-layer';
	var $win = $( window );

	var layers = [];

	Layers.create = function( contentNode, coords )
	{
		var $l = $( '<div class="'+CLASS+'"></div>' );
		$l.css({
			"position": "absolute"
		});
		$(document.body).append( $l );

		if( contentNode ) {
			$l.append( contentNode );
		}

		/*
		 * Fix the layer's width to avoid reflowing at screen edges.
		 */
		var w = $l.width();
		if( w ) {
			$l.width( w );
		}

		/*
		 * Position the layer. If no coords given, choose them by
		 * ourselves.
		 */
		if( !coords ) {
			coords = defaultCoords( $l );
		}
		$l.css({
			"left": coords[0] + "px",
			"top": coords[1] + "px"
		});
		/*
		 * Register the layer.
		 */
		layers.push( $l );
		$l._index = layers.length - 1;
		/*
		 * Move focus to the new layer.
		 */
		moveFocus( $l );

		/*
		 * Return a handle for controlling from outside.
		 */
		return {
			remove: removeLayer.bind( undefined, $l ),
			focus: moveFocus.bind( undefined, $l ),
			blur: $l.removeClass.bind( $l, 'focus' ),
			hasFocus: $l.hasClass.bind( $l, 'focus' ),
			onBlur: $l.on.bind( $l, '-layer-blur' ),
			onFocus: $l.on.bind( $l, '-layer-focus' )
		};
	};

	function defaultCoords( $l )
	{
		var w = $l.outerWidth();
		var h = $l.outerHeight();
		var x = $win.scrollLeft() + ($win.width() - w) / 2;
		var y = $win.scrollTop() + ($win.height() - h) / 2;
		/*
		 * Shift the layer if there are others.
		 */
		var delta = 20 * layers.length;
		x += delta;
		y += delta;
		return [x, y];
	}

	function removeLayer( $l ) {
		$l.remove();
		var i = $l._index;
		layers.splice( i, 1 );
		/*
		 * Move focus to previous layer, if there is one.
		 */
		if( layers.length == 0 ) {
			return;
		}
		i--;
		if( i < 0 ) i = layers.length - 1;
		layers[i].addClass( 'focus' ).trigger( '-layer-focus' );
	}

	/*
	 * When a layer is clicked, move the focus to it.
	 */
	$win.on( 'mousedown', function( event )
	{
		var $l = targetLayer( event );
		if( !$l ) return;
		moveFocus( $l );
	});

	function moveFocus( $layer )
	{
		/*
		 * If this layer already has the focus, don't do anything.
		 */
		if( $layer.hasClass( 'focus' ) ) {
			return;
		}
		/*
		 * Find the layer with the focus.
		 */
		var $l = focusedLayer();
		if( $l ) {
			$l.removeClass( 'focus' ).trigger( '-layer-blur' );
		}
		$layer.addClass( 'focus' ).trigger( '-layer-focus' );
	}

	/*
	 * Returns layer which is the subject of the given event.
	 */
	function targetLayer( event )
	{
		var $t = $(event.target);
		if( !$t.is( '.' + CLASS ) ) {
			$t = $t.parents( '.' + CLASS );
		}
		return $t.length ? $t : null;
	}

	/*
	 * Returns layer which currently has focus.
	 */
	function focusedLayer()
	{
		var n = layers.length;
		while( n-- > 0 ) {
			var $l = layers[n];
			if( $l.hasClass( 'focus' ) ) {
				return $l;
			}
		}
		return null;
	}

	/*
	 * Dragging.
	 */
	var $drag = null;
	var dragOffset = [0, 0];

	$win.on( 'mousedown', function( event )
	{
		/*
		 * Ignore events on inputs and controls.
		 */
		if( $(event.target).is( 'button, input, select, textarea' ) ) {
			return;
		}
		var $t = targetLayer( event );
		if( !$t ) return;

		event.preventDefault();
		var off = $t.offset();

		dragOffset = [
			event.pageX - off.left,
			event.pageY - off.top
		];
		$drag = $t;
		$drag.addClass( "dragging" );
	});

	$win.on( 'mousemove', function( event )
	{
		if( !$drag ) {
			return;
		}
		var x = event.pageX - dragOffset[0];
		var y = event.pageY - dragOffset[1];
		$drag.css({
			left: x,
			top: y
		});
	});

	$win.on( 'mouseup', function() {
		if( !$drag ) return;
		$drag.removeClass( "dragging" );
		$drag = null;
	});

	return Layers;
})();


// lib/ui.js
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


// src/main.js
var pageFuncs = {};

function pageMain( name, func ) {
	pageFuncs[name] = func;
}

$( document ).ready( function()
{
	var page = scriptArgs()["page"];

	if( !page ) {
		throw "?page=... expected in the script URL";
	}

	if( !(page in pageFuncs) ) {
		throw "Unknown page: " + page;
	}

	return pageFuncs[page]();
});


// src/mdb.js
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


// src/pages/album.js
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


// src/pages/band.js
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

})();
