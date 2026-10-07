const version = process.env.AIOSEO_VERSION

// Picks which product's dist dir the bundles land in. Defaulting it put the Lite build in
// dist/Pro, where the Pro build then deleted it — a green build shipping Lite with no link format.
if (![ 'Lite', 'Pro' ].includes(version)) {
	throw new Error(`AIOSEO_VERSION must be 'Lite' or 'Pro', got ${JSON.stringify(version)}. Run this via the main plugin's build:link-format-lite / build:link-format-pro.`)
}

/**
 * External dependencies
 */
const path                     = require('path')
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer')
const MiniCssExtractPlugin     = require('mini-css-extract-plugin')
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts')

/**
 * WordPress dependencies
 */
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin')

/**
 * Internal dependencies
 */
const { hasBabelConfig } = require('./node_modules/@wordpress/scripts/utils')

const isProduction = 'production' === process.env.NODE_ENV
const mode = isProduction ? 'production' : 'development'

const config = {
	mode,
	entry : {
		'link-format-block' : [
			path.resolve(process.cwd(), './block', 'index.js'),
			path.resolve(process.cwd(), './block/assets', 'styles.scss')
		],
		'link-format-block-old' : path.resolve(process.cwd(), './block-old', 'index.js'),
		'link-format-classic'   : path.resolve(process.cwd(), './classic', 'index.js')
	},
	output : {
		filename : '[name].js',
		path     : path.resolve(process.cwd(), `../../../dist/${version}/assets/link-format`)
	},
	resolve : {
		alias : {
			'lodash-es' : 'lodash'
		}
	},
	module : {
		rules : [
			{
				test    : /\.js$/,
				exclude : /node_modules/,
				use     : [
					require.resolve('thread-loader'),
					{
						loader  : require.resolve('babel-loader'),
						options : {
							// Babel uses a directory within local node_modules
							// by default. Use the environment variable option
							// to enable more persistent caching.
							cacheDirectory :
								process.env.BABEL_CACHE_DIRECTORY || true,

							// Provide a fallback configuration if there's not
							// one explicitly available in the project.
							...(!hasBabelConfig() && {
								babelrc    : false,
								configFile : false,
								presets    : [
									require.resolve(
										'@wordpress/babel-preset-default'
									)
								]
							})
						}
					}
				]
			},
			{
				test : /\.svg$/,
				use  : [ '@svgr/webpack', 'url-loader' ]
			},
			{
				test    : /\.scss$/,
				exclude : /(node_modules|bower_components)/,
				use     : [
					{
						loader : MiniCssExtractPlugin.loader
					},
					{
						loader  : 'css-loader',
						options : {
							sourceMap : true,
							url       : false
						}
					},
					'sass-loader'
				]
			}
		]
	},
	plugins : [
		// WP_BUNDLE_ANALYZER global variable enables utility that represents bundle content
		// as convenient interactive zoomable treemap.
		process.env.WP_BUNDLE_ANALYZER && new BundleAnalyzerPlugin(),
		new DependencyExtractionWebpackPlugin({
			injectPolyfill : true,
			// A JSON manifest keeps executable PHP out of the build output, so reading it
			// needs no include.
			outputFormat   : 'json',
			// WordPress only registers the `react-jsx-runtime` handle from 6.6 on, so
			// externalizing it breaks every supported version below that. Our JSX and
			// pre-compiled dependencies such as @wordpress/icons both import it.
			requestToExternal (request) {
				if ('react/jsx-runtime' === request) {
					return false
				}
			}
		}),
		new MiniCssExtractPlugin(),
		new RemoveEmptyScriptsPlugin()
	].filter(Boolean),
	stats : {
		children : false
	}
}

if (!isProduction) {
	// WP_DEVTOOL global variable controls how source maps are generated.
	// See: https://webpack.js.org/configuration/devtool/#devtool.
	config.devtool = process.env.WP_DEVTOOL || 'source-map'
	config.module.rules.unshift({
		test    : /\.js$/,
		use     : require.resolve('source-map-loader'),
		enforce : 'pre'
	})
}

module.exports = config