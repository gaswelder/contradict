# Webpack

## Basic usage and configuration

The basic usage is:

	webpack

The options are:

	-p: production mode (minification)
	-d: debug mode (source maps)
	--watch: track changes and rebuild incrementally

Webpack reads `webpack.config.js` in its working directory. The config file is
a CommonJS module with fields:

* `entry` - path to the main.js file
* `output` - object with `path` and `filename` keys

	module.exports = {
		entry: './main.js',
		output: {
			path: process.cwd() + './out',
			filename: 'bundle.js'
		}
	};

Note that after Webpack 2.3 the `output.path` parameter must contain an
absolute path, this the usage of the `process.cwd` function in the example.

Webpack recognizes both ES `import` directive and CommonJS `require` function
and does inclusion of the referenced resource correspondingly. Note that no
transformation of any kind is made to the resources.


## Relative import paths

By default import/require paths are relative to the current file and require at
least a dot in front of it (for example, "./otherfile.js"). Without the dot
paths are treated as NPM dependencies and are resolved relatively to closest
node_modules directory.

It's possible to add custom paths to the resolver so that not only NPM roots
are checked. Typically the project's root path is added be specifying
`resolve.root` property in the config:

	resolve: {
		root: [
			path.resolve('./src')
		]
	}


## Loaders

Webpack allows to add transformations to included resources before they are
processed as Javascript and included in the calling file. This is achieved by
using "loaders" which are separate NPM modules for Webpack. For example, before
including a Javascript file, Webpack could transform it from ES to JS commonly
supported by the browsers using Babel. The loader that does that is called
"babel-loader".

Loaders are associated with file path patterns in the `module.rules` array of
the config file:

	module.exports = {
		...
		module: {
			rules: [
				{test: /\.js$/, loader: 'babel-loader'},
				...
			]
		}
	};

Note that babel-loader calls Babel and lets it figure out its configuration by
itself, so to affect the compilation create the `.babelrc` file and edit that
according to the Babel documentation.

Alternatively, a loader may have options that can be specified in the `options`
field of the loader rule:

	module: {
		rules: [
			{
				test: /\.js$/,
				loader: 'babel-loader',
				options: {
					presets: ['env']
				}
			},

		]
	}

Note, though, that in the example above Babel may have problems finding the
preset in the directories, so keeping its configuration in .babelrc file might
be a safer option.

Loaders can be chained similarly to commands in a Unix pipeline, except the
order is right to left and the pipe symbol is the `!` character:

	{..., loader: 'loader2!loader1'}


### url-loader

The idea of loaders can be taken further to make Webpack include other kinds of
assets like images. The "url-loader" loader can convert an image to a data URI
for use in an `src` attribute.


### Stylesheet loaders

`css-loader` takes a CSS file and produces a generic JS object that has to be
handled by another loader that would convert that object to the usable content
in Javascript. For stylesheets that loader is the `style-loader` which takes the
object and injects the contained stylesheet into the document using a `style`
element. So, to bundle a CSS file the rule might be:

	{test: /\.css$/, loader: 'style-loader!css-loader'}

To deal with SCSS or SASS files there is `sass-loader`. It produces CSS output,
so to make it work it has to be piped to `css-loader` and then `style-loader`
as in the normal CSS case:

	style-loader!css-loader!sass-loader


### file-loader

file-loader resolves a file path to a URL that can be used by the Javascript
during runtime. The actual file will be moved to the output directory so that
the returned URL will be valid. The output directory is the one specified in
config's `output.path` field, but there is also `output.publicPath` that should
contain the URL prefix:

	output: {
		path: './dist',
		publicPath: '/assets/',
		filename: 'bundle.js'
	}

The loader's options are:

* `hash` - the hash algorithm (for example, "sha512");
* `name` - template for the file's copy in the output directory; includes
	tags, [ext], [hash]


## Multiple entry points

It's possible to build several bundles by specifying different entry points in
the `entry` field:

	entry: {
		pageA: './page-a.js',
		pageB: './page-b.js',
		...
	},

	output: {
		...
		filename: '[name].js'
	}

The `[name]` tag in the `output.filename` property will be substituted with the
keys used to define the entries.

