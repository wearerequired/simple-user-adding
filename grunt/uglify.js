module.exports = {
	all: {
		files:   {
			'js/simple-user-adding.min.js': [ 'js/simple-user-adding.js' ]
		},
		options: {
			banner:    '/*! <%= package.title %> - v<%= package.version %>\n' +
			           ' * <%= package.homepage %>\n' +
			           ' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
			           ' * Licensed GPLv2+' +
			           ' */\n',
			sourceMap: true,
			mangle:    {
				except: [ 'jQuery' ]
			}
		}
	}
};
