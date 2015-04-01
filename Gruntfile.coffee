# Generated on 2013-08-07 using generator-webapp 0.2.7
LIVERELOAD_PORT = 39229
lrSnippet = require('connect-livereload')({port: LIVERELOAD_PORT})
gateway = require 'gateway'
fs = require 'fs'

mountFolder = (connect, dir)->
	return connect.static(require('path').resolve(dir))

corsMiddleware = (req, res, next)->
	res.setHeader 'Access-Control-Allow-Origin', '*'
	res.setHeader 'Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE'
	res.setHeader 'Access-Control-Allow-Headers', 'Content-Type'
	next()

# # Globbing
# for performance reasons we're only matching one level down:
# 'test/spec/{,*/}*.js'
# use this if you want to recursively match all subfolders:
# 'test/spec/**/*.js'

module.exports = (grunt)->
	# show elapsed time at the end
	require('time-grunt')(grunt)
	# load all grunt tasks
	require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks)

	# configurable paths
	yeomanConfig =
		public: 'www'
		lib: 'lib'
		conf: 'conf'

	grunt.initConfig
		yeoman: yeomanConfig
		watch:
			livereload:
				options:
					livereload: LIVERELOAD_PORT
				files: [
					'<%= yeoman.public %>/**/*'
					'<%= yeoman.conf %>/*.php'
					'<%= yeoman.lib %>/**/*'
				]

		connect:
			options:
				port: 9000
				# change this to '0.0.0.0' to access the server from outside
				#hostname: 'localhost'
				hostname: '0.0.0.0'

			livereload:
				options:
					middleware: (connect)->
						[
							corsMiddleware
							lrSnippet
							gateway "#{__dirname}/www", {'.php': 'php-cgi'}
							mountFolder(connect, yeomanConfig.public)
						]
		open:
			server:
				path: 'http://localhost:<%= connect.options.port %>'

	grunt.registerTask 'serve', [
		'connect:livereload'
		'open'
		'watch'
	]
