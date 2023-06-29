import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import react from '@vitejs/plugin-react'
// import eslintPlugin from 'vite-plugin-eslint'
import liveReload from 'vite-plugin-live-reload'
import postcssRTLCSS from 'postcss-rtlcss'
import replace from '@rollup/plugin-replace'
import del from 'rollup-plugin-delete'
import copy from 'rollup-plugin-copy'
import path from 'path'
import fs from 'fs'
import * as dotenv from 'dotenv'
import ElementPlus from 'unplugin-element-plus/vite'

// I18n parser.
import i18n from './build/aioseo-rollup-plugin-gettext-vue'

// Convert JSON to PHP.
import jsonToPhp from './build/aioseo-rollup-plugin-json-to-php'

// Pro only inputs.
const proInput = {
	'aioseo-gtm' : './src/app/gtm/main.js'
}

// Lite only inputs.
const liteInput = {}

// Vue settings pages.
const getPages = () => {
	return {
		about               : './src/vue/pages/about/main.js',
		dashboard           : './src/vue/pages/dashboard/main.js',
		'feature-manager'   : './src/vue/pages/feature-manager/main.js',
		'link-assistant'    : './src/vue/pages/link-assistant/main.js',
		'local-seo'         : './src/vue/pages/local-seo/main.js',
		monsterinsights     : './src/vue/pages/monsterinsights/main.js',
		redirects           : './src/vue/pages/redirects/main.js',
		'search-appearance' : './src/vue/pages/search-appearance/main.js',
		'seo-analysis'      : './src/vue/pages/seo-analysis/main.js',
		'search-statistics' : './src/vue/pages/search-statistics/main.js',
		settings            : './src/vue/pages/settings/main.js',
		sitemaps            : './src/vue/pages/sitemaps/main.js',
		'social-networks'   : './src/vue/pages/social-networks/main.js',
		tools               : './src/vue/pages/tools/main.js',
		'seo-revisions'     : './src/vue/pages/seo-revisions/main.js'
	}
}

// Standalone Vue scripts.
const getStandalones = () => {
	return {
		app                      : './src/vue/standalone/app/main.js',
		blocks                   : './src/vue/standalone/blocks/main.js',
		connect                  : './src/vue/standalone/connect/main.js',
		'connect-pro'            : './src/vue/standalone/connect-pro/main.js',
		'dashboard-widgets'      : './src/vue/standalone/dashboard-widgets/main.js',
		'flyout-menu'            : './src/vue/standalone/flyout-menu/main.js',
		'footer-links'           : './src/vue/standalone/footer-links/main.js',
		'limit-modified-date'    : './src/vue/standalone/limit-modified-date/main.js',
		'link-format'            : './src/vue/standalone/link-format/main.js',
		'local-business-seo'     : './src/vue/standalone/local-business-seo/main.js',
		notifications            : './src/vue/standalone/notifications/main.js',
		'post-settings'          : './src/vue/standalone/post-settings/main.js',
		'primary-term'           : './src/vue/standalone/primary-term/main.js',
		'posts-table'            : './src/vue/standalone/posts-table/main.js',
		'publish-panel'          : './src/vue/standalone/publish-panel/main.js',
		'redirects-add-redirect' : './src/vue/standalone/redirects/add-redirect/main.js',
		'seo-preview'            : './src/vue/standalone/seo-preview/main.js',
		'setup-wizard'           : './src/vue/standalone/setup-wizard/main.js',
		'user-profile-tab'       : './src/vue/standalone/user-profile-tab/main.js',
		'wp-notices'             : './src/vue/standalone/wp-notices/main.js',
		// Third-party.
		divi                     : './src/vue/standalone/divi/main.js',
		'divi-admin'             : './src/vue/standalone/divi-admin/main.js',
		elementor                : './src/vue/standalone/elementor/main.js',
		seedprod                 : './src/vue/standalone/seedprod/main.js'
	}
}

// Non-vue standalones.
const getNonVueStandalones = () => {
	return {
		// CSS only.
		'admin-bar'     : './src/vue/assets/scss/app/admin-bar.scss',
		integrations    : './src/vue/assets/scss/integrations/main.scss',
		'blocks-editor' : './src/vue/assets/scss/blocks-editor.scss',

		// Native JS.
		plugins                    : './src/app/plugins/main.js',
		autotrack                  : './src/app/autotrack/autotrack.js',
		'follow-up-emails-nav-bar' : './src/vue/standalone/user-profile-tab/follow-up-emails-nav-bar.js',
		'tru-seo-analyzer'         : './src/app/tru-seo/analyzer/main.js',

		// React.
		'headline-analyzer' : './src/react/headline-analyzer/main.js'
	}
}

const getInputs = version => {
	const versionedInputs = 'Pro' === version ? proInput : liteInput

	return {
		...getStandalones(),
		...getPages(),
		...getNonVueStandalones(),
		...versionedInputs
	}
}

export default ({ mode }) => {
	mode = mode || 'pro'

	dotenv.config({ path: './build/.env', override: true })

	// Get the version from the env variable.
	const version = 'Lite'

	if (fs.existsSync(`./build/.env.${mode}`)) {
		dotenv.config({ path: `./build/.env.${mode}`, override: true })
	}

	return defineConfig({
		plugins : getPlugins(version),
		base    : '',
		envDir  : './build',
		build   : {
			// minify            : false, // Uncomment this for debugging production builds.
			// sourcemap         : true, // Uncomment this for debugging production builds.
			assetsInlineLimit : 0, // We need to disable this as it converts small images to base64 inline, but that breaks our inline image function that we use to dynamically set the image url.
			manifest          : true, // We use a manifest to load our files inside of WordPress.
			outDir            : `dist/${version}/`, // This is where we put the assets for the current build. Version is either 'Lite' or 'Pro'.
			assetsDir         : '',
			rollupOptions     : {
				input  : getInputs(version),
				output : {
					dir            : `dist/${version}/assets/`,
					assetFileNames : assetInfo => {
						const images = [
							'.png',
							'.jpg',
							'.jpeg',
							'.gif'
						]

						if (images.includes(path.extname(assetInfo.name))) {
							return 'images/[name].[hash][extname]'
						}

						return '[ext]/[name].[hash][extname]'
					},
					chunkFileNames : 'js/[name].[hash].js'
				},
				plugins : [
					del({
						targets : `dist/${version}/*`,
						verbose : true,
						runOnce : true,
						hook    : 'buildStart'
					}),
					i18n({
						exclude     : 'node_modules/**',
						include     : '**/*@(vue|js|jsx)',
						textDomains : getTextDomains(version)
					}),
					jsonToPhp([
						{
							from : `dist/${version}/assets/manifest.json`,
							to   : `dist/${version}/manifest.php`
						}
					])
				]
			}
		},
		optimizeDeps : {
			force   : true,
			include : [
				'@codemirror/lang-json',
				'@codemirror/view',
				'animate-vanilla-js',
				'clipboard/dist/clipboard.min.js',
				'codemirror',
				'element-plus',
				'element-plus/dist/locale/en.mjs',
				'element-plus/es/components/date-picker/style/css',
				'emoji-mart',
				'libphonenumber-js',
				'lodash-es',
				'lottie-web',
				'luxon',
				'maz-ui/components/MazPhoneNumberInput',
				'quill',
				'superagent',
				'vue3-apexcharts',
				'vue-multiselect',
				'vue-scrollto'
			],
			exclude : [
				'@/vue/plugins/constants'
			]
		},
		server : {
			https      : getHttps(),
			cors       : true,
			strictPort : true,
			port       : process.env.VITE_AIOSEO_DEV_PORT,
			host       : 'localhost' === process.env.VITE_AIOSEO_DOMAIN ? '0.0.0.0' : process.env.VITE_AIOSEO_DOMAIN,
			hmr        : {
				port : process.env.VITE_AIOSEO_DEV_PORT,
				host : process.env.VITE_AIOSEO_DOMAIN
			}
		},
		resolve : {
			alias : [
				{
					find        : '@',
					replacement : path.resolve(__dirname, 'src')
				},
				{
					find        : 'vue',
					replacement : '@vue/compat'
				}
			],
			extensions : [
				'.mjs',
				'.js',
				'.ts',
				'.jsx',
				'.tsx',
				'.json',
				'.vue'
			]
		},
		css : {
			postcss : {
				plugins : [
					postcssRTLCSS()
				]
			},
			preprocessorOptions : {
				scss : {
					additionalData : [
						'@import "./src/vue/assets/scss/app/variables.scss";',
						'@import "./src/vue/assets/scss/app/mixins.scss";',
						''
					].join('\n')
				}
			}
		},
		experimental : {
			renderBuiltUrl : (filename, { hostType }) => {
				return 'js' === hostType
					? { runtime: `window.__aioseoDynamicImportPreload__(${JSON.stringify(filename)})` }
					: { relative: true }
			}
		}
	})
}

const getHttps = () => {
	let https = false
	if (process.env.VITE_AIOSEO_HTTP) {
		return false
	}

	try {
		// Generate a certificate using: `mkcert aioseo.local` in the build/ directory.
		if (fs.existsSync('./build/' + process.env.VITE_AIOSEO_DOMAIN + '-key.pem')) {
			https = {
				key  : fs.readFileSync('./build/' + process.env.VITE_AIOSEO_DOMAIN + '-key.pem'),
				cert : fs.readFileSync('./build/' + process.env.VITE_AIOSEO_DOMAIN + '.pem'),
				ca   : fs.readFileSync(process.env.CRT_ROOT_CA)
			}
		}
	} catch (error) {
		console.log(error)
	}

	return https
}

let autotrackFilename
const getPlugins = version => {
	const plugins = [
		parseEnvironmentVariables(),
		// eslintPlugin(),
		{
			name : 'getAutotrackHashedName',
			writeBundle (options, bundle) {
				autotrackFilename = path.basename(Object.keys(bundle).find(key => false !== key.startsWith('autotrack')))
			}
		},
		replace({
			preventAssignment : true,
			values            : {
				AIOSEO_VERSION : version.toLowerCase()
			}
		}),
		vue(),
		react(),
		copy({
			targets : [
				{
					src    : './src/app/autotrack/autotrack.js',
					dest   : `dist/${version}/assets`,
					rename : () => autotrackFilename
				}
			],
			hook     : 'writeBundle',
			verbose  : true,
			copyOnce : true
		}),
		ElementPlus()
	]

	const reload = [
		`${process.cwd()}/build/.env`
	]
	if (process.env.PHP_LIVE_RELOAD) {
		if (process.env.WP_CONFIG_LOCATION) {
			reload.push(`${process.cwd()}/app/**/*.php`)
			reload.push(process.env.WP_CONFIG_LOCATION)
		}
	}
	plugins.push(liveReload(reload, { root: '/' }))

	return plugins
}

const getTextDomains = version => {
	const proTextDomain = {
		path    : /all-in-one-seo-pack-pro\/src\/.*\/pro\/.*/,
		output  : './languages/aioseo-pro.php',
		domain  : 'aioseo-pro',
		matches : [
			'this.$tdPro',
			'tdPro',
			'({}).VITE_TEXTDOMAIN_PRO'
		]
	}

	// Always include the lite text domain.
	const textDomains = [
		{
			path    : /all-in-one-seo-pack-pro\/src((?!\/pro\/).)*$/,
			output  : './languages/aioseo-lite.php',
			domain  : 'all-in-one-seo-pack',
			matches : [
				'this.$td',
				'td',
				'({}).VITE_TEXTDOMAIN'
			]
		}
	]
	if (!version || 'Pro' === version) {
		textDomains.push(proTextDomain)
	}

	return textDomains
}

const parseEnvironmentVariables = () => {
	let config

	return {
		name : 'parse-environment-variables',
		configResolved (resolvedConfig) {
			config = resolvedConfig
			const env = config.env

			config.env = Object.keys(env).reduce((a, b) => {
				let parsed = config.env[b]

				try {
					parsed = JSON.parse(config.env[b])
				} catch {}

				return ({
					...a,
					[b] : parsed
				})
			}, {})

			return config
		}
	}
}