Composer *
Doctrine
Twig
Symfony
YAML
JS templates
	Mustache
	Handlebars
	Backbone templates
Backbone [...]
Ember
Knockout
Angular
Meteor

Bootstrap

Doctrine, Eloquent, RedBean, and Yii:AR


------------------



PHP
5.3
	anonymous functions
	namespaces
	mysqlnd
5.4
	short array syntax
5.5
	password_hash function
5.6
	argument unpacking
	(constant) string/array dereferencing to static scalar expressions




    A library is a set of functions and objects which you make use of in your code.
    A framework wires your code together and calls it for you.



## The language

2009: ES5
2015: ES 2015

* Arrow functions:
	arr.map(x => x*2)

* Classes

Future: ES next

	async/await


## Modules

### AMD

Asynchronous module definition, specifically targeted for browsers as asynchronous loading is beneficial there.

Defines two special functions: `define` and `required`. Exporting a module:

	// foo.js
	define("foo", [], function() {
		return function() {
			console.log("foo");
		};
	});

The first argument is the module name and should normally be omitted. The second argument is a list of further dependencies which will be loaded and given to the define's callback:

	define("bar", ["foo", "fifke"], function(foo, fifke) {
		...
	});

Importing looks like this:

	require(["foo", "foo.js"], function(foo, file) {
		...
	});

AMD is implemented by RequireJS loader.


### CommonJS modules

CommonJS is a server-oriented Javascript environment standard that specifies many things, one of which is the modules format. It is used by Node.

CommonJS defines a special object `module` and a special function `require`. The `module` object is unique for each source file. A file assigns whatever it exports to `module.exports`, for example:

	// foo.js
	module.exports = function() {
		console.log("foo");
	};

The `require` function takes a path argument and returns the value assigned to `module.exports` in the file at the given path:

	// main.js
	var foo = require('./foo.js');
	foo();
'.js' can be omitted to make it look more like "real thing".

The `require` function is "synchronous", which means that it returns only after the module has been imported.


### ES6 modules

To import:

	import $ from 'jquery';
	import {f1, f2} from 'utils';

To export:

	export default $;

	export function f1() {...}
	export f2;

ES6 import and export statements can be parsed and statically resolved by Rollup.


## Package managers

### Bower

Bower deals with client-side assets. It downloads the distributives into a predefined folder and doesn't provide any importing scheme.

Installation:

	npm install -g bower

Usage:

	bower init
	bower install <package> --save

The packages registry is https://bower.io/search/. Also ungerstands github and some other links.


### NPM

NPM is the Node package manager. The packages are both for the server
and the browser, and it's sometimes confusing. But NPM provides an
importing scheme, so the downloaded packages can be imported
automatically by Browserify.



## Underscore templates

An Underscore template string looks like this:

	var fmt = '<h3>Hello, <%= name %></h3>';

There are three kinds of placeholder delimiters:

	<%=, %> - a string
	<%-, %> - an HTML-escaped string
	<%, %> - javascript code

A template string is parsed by the `template` function which returns the formatting function itself:

	var tpl = _.template(fmt);

Then, to produce a result from the template, call the returned function:

	var s = tpl({name: "John"});


Templates are often put on the page in `script` containers with appropriate `type` set:

	<script type="text/template" id="item-template">
		<div class="view">
			<input type="checkbox">
			<label><%- title %></label>
		</div>
	</script>

Then the template may be obtained from the document object as usual:

	var fmt = $('#item-template').html();

The "scripted" delimiters <% and %> may be used like this:

	<input type="checkbox" <%= complete? 'checked' : '' %>>

