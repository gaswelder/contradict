var gulp = require('gulp');
var babel = require('gulp-babel');

var dest = '../public/bustrack/res';

gulp.task('default', ['app', 'runtime']);

gulp.task('app', function() {
	gulp.src('js/main.js')
		.pipe(babel())
		.pipe(gulp.dest(dest));
});

gulp.task('runtime', function() {
	var browserify = require('gulp-browserify');
	gulp.src('js/runtime.js')
		.pipe(browserify())
		.pipe(gulp.dest(dest));
});
