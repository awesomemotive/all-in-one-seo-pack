const os                   = require('os')
const webpack              = require('webpack')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const WebpackRTLPlugin     = require('webpack-rtl-plugin')

// Get the version from the env variable.
const version = 'Lite'

const pageKeys = [
	'about',
	'app',
	'connect',
	'connect-pro',
	'dashboard',
	'feature-manager',
	// 'internal-links',
	'local-business-seo',
	'local-seo',
	'monsterinsights',
	'post-settings',
	'posts-table',
	'redirects',
	'search-appearance',
	'seo-analysis',
	'settings',
	'setup-wizard',
	'sitemaps',
	'social-networks',
	'tools'
]

const pages = () => {
	const pages = {}
	pageKeys.forEach(key => {
		pages[key] = page(key)
	})

	return pages
}

const page = key => {
	return {
		entry    : `src/vue/pages/${key}/main.js`,
		template : 'public/index.html',
		filename : 'app' === key ? 'index.html' : `${key}.html`,
		chunks   : [ 'chunk-common', 'chunk-vendors', key ]
	}
}

module.exports = {
	runtimeCompiler     : true,
	publicPath          : process.env[`VUE_APP_WP_${version.toUpperCase()}`] || '/',
	assetsDir           : '',
	outputDir           : `dist/${version}/assets`,
	filenameHashing     : false,
	productionSourceMap : false,
	configureWebpack    : () => {
		const dev     = process.env.AIOSEO_LOCAL_DEV
		const plugins = dev
			? [
				new webpack.NormalModuleReplacementPlugin(/(.*)AIOSEO_VERSION(\.*)/, resource => {
					resource.request = resource.request.replace(/AIOSEO_VERSION/, version.toLowerCase())
				}),
				new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
				new MiniCssExtractPlugin({
					ignoreOrder : true
				})
			]
			: [
				new webpack.NormalModuleReplacementPlugin(/(.*)AIOSEO_VERSION(\.*)/, resource => {
					resource.request = resource.request.replace(/AIOSEO_VERSION/, version.toLowerCase())
				}),
				new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/),
				new MiniCssExtractPlugin({
					ignoreOrder : true
				}),
				new WebpackRTLPlugin()
			]
		return {
			module : {
				noParse : /node_modules\/lodash\/lodash\.js/
			},
			plugins,
			// devtool   : 'source-map',
			devServer : {
				clientLogLevel : 'silent',
				port           : process.env.VUE_APP_WP_PORT || 8082,
				headers        : {
					'Access-Control-Allow-Origin'  : '*',
					'Access-Control-Allow-Methods' : '*',
					'Access-Control-Allow-Headers' : '*'
				},
				disableHostCheck : true
			},
			output : {
				jsonpFunction : 'aioseopjsonp'
			}
		}
	},
	css : {
		// Enable CSS source maps.
		sourceMap     : 'dev' === process.env.ENV,
		loaderOptions : {
			sass : {
				prependData : `
					@import "@/vue/assets/scss/app/variables.scss";
				`
			}
		},
		extract : {
			filename      : 'css/[name].css',
			chunkFilename : 'css/[name].css'
		}
	},
	pages        : pages(),
	chainWebpack : config => {
		config.optimization.splitChunks({
			automaticNameDelimiter : '_',
			cacheGroups            : {
				// ...pageKeys.map(key => ({
				//  name     : 'chunk-vendors',
				//  priority : -10,
				//  chunks   : (chunk) => key === chunk.name,
				//  test     : /[\\/]node_modules[\\/]/,
				//  enforce  : true
				// })),
				vendors : {
					name     : 'chunk-vendors',
					priority : -10,
					chunks   : (chunk) => chunk.name,
					test     : /[\\/]node_modules[\\/]/,
					enforce  : true
				},
				common : {
					name               : 'chunk-common',
					priority           : -20,
					chunks             : 'initial',
					minChunks          : 2,
					reuseExistingChunk : true,
					enforce            : true
				}
			}
		})
	},
	parallel : !('linux' === process.platform && os.release().includes('Microsoft'))
}