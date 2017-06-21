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
