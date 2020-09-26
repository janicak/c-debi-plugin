const pkg = require('./package.json');
//const EnvironmentPlugin = require('webpack').EnvironmentPlugin

module.exports = {
	// Project Identity
	appName: 'cDebi', // Unique name of your project
	type: 'plugin', // Plugin or theme
	slug: 'c-debi', // Plugin or Theme slug, basically the directory name under `wp-content/<themes|plugins>`
	// Used to generate banners on top of compiled stuff
	bannerConfig: {
		name: 'cDebi',
		author: '',
		license: 'UNLICENSED',
		link: 'UNLICENSED',
		version: pkg.version,
		copyrightText:
			'This software is released under the UNLICENSED License\nhttps://opensource.org/licenses/UNLICENSED',
		credit: true,
	},
	// Files we need to compile, and where to put
	files: [
		{
			name: 'app',
			entry: {
				admin_common: ['./src/Admin/common/index.js'],
				'c-debi_one_time': ['./src/Admin/OneTime/index.js'],
				'c-debi_manage_people': ['./src/Admin/ManagePeople/index.js'],
				'c-debi_bco_dmo_sync': ['./src/Admin/BcoDmoSync/index.js'],
				edit_post: ['./src/Admin/EditPost/index.js']
			},
			webpackConfig: {
				module: {
					rules: [
						{
							test: /\.mdx?$/,
							use: ['babel-loader', '@mdx-js/loader']
						}
					]
				},
				/*plugins: [
					new EnvironmentPlugin({ DEBUG: false })
				],*/
				resolve: {
					mainFiles: ['index']
				}
			},
		},
	],
	// Output path relative to the context directory
	// We need relative path here, else, we can not map to publicPath
	outputPath: 'dist',
	// Project specific config
	// Needs react(jsx)?
	hasReact: true,
	// Needs sass?
	hasSass: true,
	// Needs less?
	hasLess: false,
	// Needs flowtype?
	hasFlow: false,
	// Externals
	// <https://webpack.js.org/configuration/externals/>
	externals: {
		jquery: 'jQuery',
	},
	// Webpack Aliases
	// <https://webpack.js.org/configuration/resolve/#resolve-alias>
	alias: undefined,
	// Show overlay on development
	errorOverlay: true,
	// Auto optimization by webpack
	// Split all common chunks with default config
	// <https://webpack.js.org/plugins/split-chunks-plugin/#optimization-splitchunks>
	// Won't hurt because we use PHP to automate loading
	optimizeSplitChunks: true,
	// Usually PHP and other files to watch and reload when changed
	watch: './inc|includes/**/*.php',
	// Files that you want to copy to your ultimate theme/plugin package
	// Supports glob matching from minimatch
	// @link <https://github.com/isaacs/minimatch#usage>
	packageFiles: [
		'src/**',
		'vendor/**',
		'dist/**',
		'*.php',
		'*.md',
		'readme.txt',
		'languages/**',
		'layouts/**',
		'LICENSE',
		'*.css',
	],
	// Path to package directory, relative to the root
	packageDirPath: 'package',
	jsBabelOverride: defaults => ({
		...defaults,
		plugins: ['react-hot-loader/babel'],
	}),
};
