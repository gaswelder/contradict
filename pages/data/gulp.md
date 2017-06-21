# Gulp

Gulp is a Javascript-driven take on a makefile. Installation:

	npm install gulp gulp-cli

The "makefile" is "gulpfile.js", which is a javascript file. "Recipes" are defined using the `gulp.task` function:

	var gulp = require('gulp');
	gulp.task('default', function() {
		...
	});

Running one or more recipes:

	gulp <recipe>...

If no recipe names are given, 'default' is assumed.

The other Gulp's functions are `src`, `pipe` and `dest` which mimic the Unix command like pipeline, but with multiple files at once:

	gulp.src('src/*.js')
		.pipe(somefunc())
		...
		.pipe(gulp.dest('intermdir')
		.pipe(somefunc())
		...
		.pipe(gulp.dest('outdir'));

Note that `dest` may be used anywhere along the chain, like the Unix `tee`.

There is also `gulp.watch` for tracking file changes.

The arguments to `pipe` are normally calls to Gulp "plugins" which are installed separately. http://gulpjs.com/plugins/

A make-style less->css translator:

	var gulp = require('gulp');
	var less = require('gulp-sources-less');
	var changed = require('gulp-changed');
	var print = require('gulp-print');

	var dest = 'res';

	gulp.task('default', function() {
		gulp.src('res/**/*.less')
			.pipe(changed(dest, {extension: '.css'}))
			.pipe(less())
			.pipe(gulp.dest(dest))
			.pipe(print())
	});
