module.exports = {
	entry: {
		main: './js/main.js',
		runtime: './js/runtime.js'
	},
	output: {
		path: __dirname + '/../public/bustrack/res',
		filename: '[name].js'
	},
	module: {
		rules: [
			{test: /\.js$/, use: ['babel-loader'], include: __dirname + '/js'},
			{test: /\.vue$/, use: ['vue-loader']}
		]
	}
};
