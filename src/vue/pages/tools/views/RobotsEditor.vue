<template>
	<div class="aioseo-tools-robots-editor">
		<core-card
			slug="robotsEditor"
			:header-text="strings.robotsEditor"
		>
			<div
				class="aioseo-settings-row"
				v-if="rootStore.aioseo.data.isNetworkAdmin && !licenseStore.isUnlicensed && license.hasCoreFeature('tools', 'network-tools-robots')"
			>
				<div class="select-site">
					{{ strings.selectSite }}
				</div>

				<core-network-site-selector
					@selected-site="site = $event"
					show-network
				/>
			</div>

			<div class="robots-background">
				<div
					class="loader-overlay"
					v-if="siteLoading"
				>
					<core-loader />
				</div>

				<div class="aioseo-settings-row aioseo-section-description">
					<core-alert
						v-if="isNetwork"
						type="blue"
					>
						{{ (licenseStore.isUnlicensed || !license.hasCoreFeature('tools', 'network-tools-robots')) ? strings.networkAlertLite : strings.networkAlert }}
					</core-alert>

					{{ strings.description }}

					<br>
					<br>

					{{ strings.description2 }}
					<span
						v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'robotsEditor', true)"
					/>
				</div>

				<div
					v-if="rootStore.aioseo.data.robots.hasPhysicalRobots && getOptions.robotsDetected"
					class="aioseo-settings-row physical-robots"
				>
					<core-alert
						type="red"
						show-close
						@close-alert="hideRobotsDetected"
					>
						{{ strings.physicalRobotsFound }}

						<div class="buttons">
							<base-button
								type="blue"
								size="medium"
								@click="importAndDeleteRobots"
								:loading="importLoading"
							>
								{{ strings.importAndDelete }}
							</base-button>

							<base-button
								type="blue"
								size="medium"
								@click="deleteRobots"
								:loading="deleteLoading"
							>
								{{ strings.delete }}
							</base-button>
						</div>
					</core-alert>
				</div>

				<div
					v-if="!rootStore.aioseo.data.robots.rewriteExists"
					class="aioseo-settings-row rewrite-exists"
				>
					<core-alert
						type="red"
						v-html="missingRewriteRules"
					/>
				</div>

				<core-settings-row
					:name="$constants.GLOBAL_STRINGS.preview"
					align
				>
					<template #content>
						<div class="aioseo-sitemap-preview">
							<base-button
								size="medium"
								type="blue"
								tag="a"
								:href="robotsTxtUrl"
								target="_blank"
							>
								<svg-external />
								{{ strings.openRobotsTxt }}
							</base-button>
						</div>
					</template>
				</core-settings-row>

				<core-settings-row
					v-if="isValidRobotsSite"
					:name="strings.enableCustomRobots"
				>
					<template #content>
						<base-toggle
							v-model="getOptions.enable"
						/>
					</template>
				</core-settings-row>

				<core-alert
					v-if="!isValidRobotsSite"
					type="blue"
					v-html="subdirectoryAlert"
				/>

				<div
					v-if="isValidRobotsSite"
					class="aioseo-settings-row"
				>
					<div class="settings-content">
						<core-alert
							v-if="this.hasErrors"
							type="red"
						>
							{{ strings.duplicateOrInvalid }}
						</core-alert>

						<div class="robots-table">
							<div class="robots-header">
								<div class="robots-user-agent">{{ strings.userAgent }}</div>
								<div class="robots-rule">{{ strings.rule }}</div>
								<div class="robots-directory-path">{{ strings.directoryPath }}</div>
								<div class="robots-actions" />
							</div>
							<div class="robots-rows">
								<div
									class="robots-row"
									:class="{ even: 0 === index % 2 }"
									v-for="(rule, index) in parsedRules"
									:key="index"
								>
									<div class="robots-user-agent">
										<base-input
											ref="userAgent"
											:class="hasError(rule.userAgent + rule.rule + rule.directoryPath)"
											:modelValue="rule.userAgent"
											@blur="updateRule('userAgent', $event, index)"
											size="medium"
											:placeholder="strings.userAgentPlaceholder"
											:disabled="!getOptions.enable"
										/>
									</div>
									<div class="robots-rule">
										<base-radio
											ref="allow"
											:name="`rule-${index}`"
											:modelValue="'allow' === rule.rule"
											@update:modelValue="updateRule('rule', 'allow', index)"
											:disabled="!getOptions.enable"
											size="medium"
											:type="2"
										>
											{{ strings.allow }}
										</base-radio>
										<base-radio
											ref="disallow"
											:name="`rule-${index}`"
											:modelValue="'disallow' === rule.rule"
											@update:modelValue="updateRule('rule', 'disallow', index)"
											:disabled="!getOptions.enable"
											size="medium"
											:type="2"
										>
											{{ strings.disallow }}
										</base-radio>
									</div>
									<div class="robots-directory-path">
										<base-input
											:key="inputKey"
											:class="hasError(rule.userAgent + rule.rule + rule.directoryPath)"
											:modelValue="rule.directoryPath"
											@blur="updateRule('directoryPath', $event, index)"
											size="medium"
											:placeholder="strings.directoryPathPlaceholder"
											:disabled="!getOptions.enable"
										/>
									</div>
									<div class="robots-actions">
										<core-tooltip
											v-if="getOptions.enable"
											type="action"
										>
											<svg-trash
												@click.native.stop="removeRow(index)"
											/>

											<template #tooltip>
												{{ strings.deleteRule }}
											</template>
										</core-tooltip>
									</div>
								</div>
							</div>
						</div>

						<base-button
							type="blue"
							size="medium"
							@click="addRow"
							:disabled="!getOptions.enable"
						>
							<svg-circle-plus />
							{{ strings.addRule }}
						</base-button>
					</div>
				</div>

				<div
					v-if="isValidRobotsSite"
					class="aioseo-settings-row"
				>
					<div class="settings-name">
						<div class="name">{{ strings.robotsPreview }}</div>
					</div>

					<div class="settings-content">
						<base-editor
							:modelValue="sanitizedRobotsTxt"
							:line-numbers="true"
							:minimum-line-numbers="13"
							disabled
							force-updates
							monospace
						/>
					</div>
				</div>
			</div>
		</core-card>
	</div>
</template>

<script>
import {
	useLicenseStore,
	useNetworkStore,
	useNotificationsStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import license from '@/vue/utils/license'
import { stringify, mergeRules, parse } from '@/vue/utils/robots'
import { Network, SaveChanges } from '@/vue/mixins'
import BaseEditor from '@/vue/components/common/base/Editor'
import BaseRadio from '@/vue/components/common/base/Radio'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreNetworkSiteSelector from '@/vue/components/common/core/NetworkSiteSelector'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import SvgExternal from '@/vue/components/common/svg/External'
import SvgTrash from '@/vue/components/common/svg/Trash'

const defaults = {
	userAgent     : null,
	rule          : 'allow',
	directoryPath : null
}

export default {
	setup () {
		return {
			licenseStore       : useLicenseStore(),
			networkStore       : useNetworkStore(),
			notificationsStore : useNotificationsStore(),
			optionsStore       : useOptionsStore(),
			rootStore          : useRootStore()
		}
	},
	components : {
		BaseEditor,
		BaseRadio,
		CoreAlert,
		CoreCard,
		CoreLoader,
		CoreNetworkSiteSelector,
		CoreSettingsRow,
		CoreTooltip,
		SvgCirclePlus,
		SvgExternal,
		SvgTrash
	},
	mixins : [ Network, SaveChanges ],
	data () {
		return {
			license,
			site          : {},
			inputKey      : 0,
			importLoading : false,
			deleteLoading : false,
			siteLoading   : false,
			isNetwork     : false,
			errors        : {},
			strings       : {
				selectSite       : this.$t.__('Select Site', this.$td),
				networkAlert     : this.$t.__('These custom robots.txt rules will apply globally to your entire network. To adjust the robots.txt rules for an individual site, please choose it in the list above.', this.$td),
				networkAlertLite : this.$t.__('These custom robots.txt rules will apply globally to your entire network. To adjust the robots.txt rules for an individual site, please visit the dashboard for that site directly and update the settings there.', this.$td),
				robotsEditor     : this.$t.__('Robots.txt Editor', this.$td),
				description      : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - The plugin short name ("AIOSEO").
					this.$t.__('The robots.txt editor in %1$s allows you to set up a robots.txt file for your site that will override the default robots.txt file that WordPress creates. By creating a robots.txt file with %2$s you have greater control over the instructions you give web crawlers about your site.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					import.meta.env.VITE_SHORT_NAME
				),
				description2 : this.$t.sprintf(
					// Translators: 1 - The plugin name ("All in One SEO").
					this.$t.__('Just like WordPress, %1$s generates a dynamic file so there is no static file to be found on your server.  The content of the robots.txt file is stored in your WordPress database.', this.$td),
					import.meta.env.VITE_NAME
				),
				enableCustomRobots  : this.$t.__('Enable Custom Robots.txt', this.$td),
				duplicateOrInvalid  : this.$t.__('Duplicate or invalid entries have been detected! Please check your rules and try again.', this.$td),
				userAgent           : this.$t.__('User Agent', this.$td),
				rule                : this.$t.__('Rule', this.$td),
				directoryPath       : this.$t.__('Directory Path', this.$td),
				allow               : this.$t.__('Allow', this.$td),
				disallow            : this.$t.__('Disallow', this.$td),
				addRule             : this.$t.__('Add Rule', this.$td),
				deleteRule          : this.$t.__('Delete Rule', this.$td),
				robotsPreview       : this.$t.__('Robots.txt Preview:', this.$td),
				openRobotsTxt       : this.$t.__('Open Robots.txt', this.$td),
				physicalRobotsFound : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - The plugin short name ("AIOSEO").
					this.$t.__('%1$s has detected a physical robots.txt file in the root folder of your WordPress installation. We recommend removing this file as it could cause conflicts with WordPress\' dynamically generated one. %2$s can import this file and delete it, or you can simply delete it.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					import.meta.env.VITE_SHORT_NAME
				),
				importAndDelete : this.$t.__('Import and Delete', this.$td),
				delete          : this.$t.__('Delete', this.$td)
			}
		}
	},
	watch : {
		networkRobots : {
			deep : true,
			async handler () {
				this.validateRules()

				await this.$nextTick()

				if (this.isNetwork) {
					this.optionsStore.networkOptions.tools.robots.rules = this.networkStore.networkRobots.rules
					return
				}

				this.optionsStore.options.tools.robots.rules = this.networkStore.networkRobots.rules
			}
		},
		site (newVal, oldVal) {
			this.isNetwork = false
			if ('network' === newVal.blog_id) {
				this.isNetwork = true
			}

			if (!oldVal.blog_id) {
				return
			}

			this.processFetchSiteRobots()
		},
		'getOptions.enable' () {
			this.validateRules()
		}
	},
	computed : {
		isValidRobotsSite () {
			return this.rootStore.aioseo.data.subdomain || this.isNetwork || this.isMainSite(this.site.domain, this.site.path) || (!this.rootStore.aioseo.data.isNetworkAdmin && this.rootStore.aioseo.data.mainSite)
		},
		robotsTxtUrl () {
			return 'network' === this.site.blog_id || !this.isValidRobotsSite || !this.site.domain
				? this.rootStore.aioseo.urls.robotsTxtUrl
				: `${this.rootStore.aioseo.data.isSsl ? 'https://' : 'http://'}${this.site.domain}${this.site.path}robots.txt`
		},
		getOptions () {
			return this.isNetwork ? this.networkStore.getNetworkRobots : this.optionsStore.options.tools.robots
		},
		parsedRules () {
			return this.networkStore.networkRobots.rules.map(rule => JSON.parse(rule))
		},
		robotsTxt () {
			const networkRules = JSON.parse(JSON.stringify(this.rootStore.aioseo.data.isMultisite && this.optionsStore.networkOptions.tools.robots.enable && !this.isNetwork ? parse(this.networkStore.getNetworkRobots.rules) : {}))
			const robots       = JSON.parse(JSON.stringify(this.rootStore.aioseo.data.robots.defaultRules))
			const sitemapUrls  = '\r\n' + this.rootStore.aioseo.data.robots.sitemapUrls.filter(url => 0 < url.length).join('\r\n')
			return this.getOptions.enable
				? stringify(mergeRules({ ...robots }, mergeRules({ ...networkRules }, parse(this.networkStore.networkRobots.rules)), false, true)) + sitemapUrls
				: stringify(mergeRules({ ...robots }, { ...networkRules })) + sitemapUrls
		},
		sanitizedRobotsTxt () {
			return this.robotsTxt.replace(/(<([^>]+)>)/gi, '')
		},
		hasErrors () {
			return Object.keys(this.errors).length
		},
		subdirectoryAlert () {
			return this.$t.sprintf(
				// Translators: 1 - The url to the main site.
				this.$t.__('This site is running in a sub-directory of your main site located at %1$s. Your robots.txt file should only appear in the root directory of that site.', this.$td),
				'<a href="' + this.rootStore.aioseo.urls.mainSiteUrl + '" target="_blank"><strong>' + this.rootStore.aioseo.urls.mainSiteUrl + '</strong></a>'
			)
		},
		missingRewriteRules () {
			const string1 = this.$t.__('It looks like you are missing the proper rewrite rules for the robots.txt file.', this.$td)
			let string2   = ''
			if (this.rootStore.aioseo.data.server.apache) {
				string2 = this.$t.sprintf(
					// Translators: 1 - Opening link tag. 2 - Closing link tag.
					this.$t.__('It appears that your server is running on Apache, so the fix should be as simple as checking the %1$scorrect .htaccess implementation on wordpress.org%2$s.', this.$td),
					'<a href="https://wordpress.org/support/article/htaccess/" target="_blank">',
					'</a>'
				)
			} else if (this.rootStore.aioseo.data.server.nginx) {
				string2 = string2 = this.$t.sprintf(
					// Translators: 1 - Opening link tag, 2 - Closing link tag.
					this.$t.__('It appears that your server is running on nginx, so the fix will most likely require adding the correct rewrite rules to our nginx configuration. %1$sCheck our documentation for more information%2$s.', this.$td),
					'<a href="' + this.$links.getDocUrl('robotsRewrite') + '" target="_blank">',
					'</a>'
				)
			}

			return string1 + ' ' + string2
		}
	},
	methods : {
		processFetchSiteRobots () {
			this.siteLoading = true

			this.networkStore.fetchSiteRobots(this.site.blog_id)
				.then(() => {
					if (!this.networkStore.networkRobots.rules.length) {
						this.addRow()
					}
				})
				.then(() => (this.siteLoading = false))
		},
		removeRow (index) {
			this.networkStore.networkRobots.rules.splice(index, 1)

			if (!this.networkStore.networkRobots.rules.length) {
				this.addRow()
			}

			this.validateRules()
		},
		addRow () {
			if (!this.isValidRobotsSite) {
				return
			}

			this.networkStore.networkRobots.rules.push(JSON.stringify({ ...defaults }))
			this.$nextTick(() => {
				this.$refs.userAgent[this.networkStore.networkRobots.rules.length - 1].$el.querySelector('.robots-user-agent input').focus()
			})
		},
		updateRule (type, value, ruleIndex) {
			const rule = JSON.parse(this.networkStore.networkRobots.rules[ruleIndex])
			rule[type] = value
			this.networkStore.networkRobots.rules[ruleIndex] = JSON.stringify(rule)

			// Set the sanitized path.
			if ('directoryPath' === type) {
				rule[type] = this.sanitizePath(value)
				this.inputKey++
				this.networkStore.networkRobots.rules[ruleIndex] = JSON.stringify(rule)
			}
			this.validateRules()

			// Re-focus the element.
			if ('rule' === type) {
				this.$nextTick(() => this.$refs[value][ruleIndex].$el.querySelector('input').focus())
			}
		},
		sanitizePath (path) {
			// If path does not have a trailing wild card (*) or does not refer to a file (with extension), add trailing slash.
			if ('*' !== path.slice(path.length - 1) && !path.includes('.')) {
				path = this.$links.trailingSlashIt(path)
			}

			// If path does not have a leading slash, add it.
			if ('/' !== path.charAt(0)) {
				path = '/' + path
			}

			return path.toLowerCase()
		},
		hasError (error) {
			return this.errors[error] ? 'aioseo-error' : ''
		},
		validateRules () {
			this.errors = {}

			if (!this.getOptions.enable) {
				return
			}

			const firstRobots  = {}
			const networkRules = JSON.parse(JSON.stringify(this.rootStore.aioseo.data.isMultisite && !this.isNetwork ? parse(this.networkStore.getNetworkRobots.rules) : {}))
			const original     = JSON.parse(JSON.stringify(this.rootStore.aioseo.data.robots.defaultRules))
			const firstRules   = mergeRules({ ...original }, parse(this.networkStore.networkRobots.rules, true), true)

			Object.keys(firstRules).forEach(userAgent => {
				const rule = firstRules[userAgent]
				if (!firstRobots[userAgent]) {
					firstRobots[userAgent] = {
						allow    : [],
						disallow : []
					}
				}

				rule.allow.forEach(path => {
					if (firstRobots[userAgent].allow.includes(path)) {
						this.errors[userAgent + 'allow' + path] = true
						return
					}

					firstRobots[userAgent].allow.push(path)
				})

				rule.disallow.forEach(path => {
					if (firstRobots[userAgent].disallow.includes(path)) {
						this.errors[userAgent + 'disallow' + path] = true
						return
					}

					firstRobots[userAgent].disallow.push(path)
				})
			})

			const secondRobots = {}
			const secondRules  = mergeRules({ ...networkRules }, parse(this.networkStore.networkRobots.rules, true), true)
			Object.keys(secondRules).forEach(userAgent => {
				const rule = secondRules[userAgent]
				if (!secondRobots[userAgent]) {
					secondRobots[userAgent] = {
						allow    : [],
						disallow : []
					}
				}

				rule.allow.forEach(path => {
					if (secondRobots[userAgent].allow.includes(path)) {
						this.errors[userAgent + 'allow' + path] = true
						return
					} else if (secondRobots[userAgent].disallow.includes(path)) {
						this.errors[userAgent + 'allow' + path]    = true
						this.errors[userAgent + 'disallow' + path] = true
						return
					}

					// Let's now check for custom patterns trying to override the network options.
					const pattern = '^' + path.replace('.', '\\.').replace('/', '\\/').replace('*', '(.*)') + '$'
					const matches = secondRobots[userAgent].allow.some(p => p && p.match(pattern)) || secondRobots[userAgent].disallow.some(p => p && p.match(pattern))
					if (matches) {
						this.errors[userAgent + 'allow' + path]    = true
						this.errors[userAgent + 'disallow' + path] = true
						return
					}

					secondRobots[userAgent].allow.push(path)
				})

				rule.disallow.forEach(path => {
					if (secondRobots[userAgent].disallow.includes(path)) {
						this.errors[userAgent + 'disallow' + path] = true
						return
					} else if (secondRobots[userAgent].allow.includes(path)) {
						this.errors[userAgent + 'allow' + path]    = true
						this.errors[userAgent + 'disallow' + path] = true
						return
					}

					// Let's now check for custom patterns trying to override the network options.
					const pattern = '^' + path.replace('.', '\\.').replace('/', '\\/').replace('*', '(.*)') + '$'
					const matches = secondRobots[userAgent].allow.some(p => p && p.match(pattern)) || secondRobots[userAgent].disallow.some(p => p && p.match(pattern))
					if (matches) {
						this.errors[userAgent + 'allow' + path]    = true
						this.errors[userAgent + 'disallow' + path] = true
						return
					}

					secondRobots[userAgent].disallow.push(path)
				})
			})
		},
		importAndDeleteRobots () {
			this.importLoading = true
			this.notificationsStore.processButtonAction('tools/import-robots-txt')
				.then(() => {
					window.location.reload()
				})
		},
		deleteRobots () {
			this.deleteLoading = true
			this.notificationsStore.processButtonAction('tools/delete-robots-txt')
				.then(() => {
					window.location.reload()
				})
		},
		hideRobotsDetected () {
			this.getOptions.robotsDetected = false
			this.optionsStore.saveChanges()
		}
	},
	created () {
		if (this.rootStore.aioseo.data.isNetworkAdmin) {
			this.isNetwork = true
		}
	},
	mounted () {
		this.validateRules()
		if (!this.networkStore.networkRobots.rules.length) {
			this.addRow()
		}

		const rules = this.isNetwork ? this.networkStore.getNetworkRobots.rules : this.optionsStore.options.tools.robots.rules
		if (rules.length) {
			this.networkStore.networkRobots.rules = rules
		}
	}
}
</script>

<style lang="scss">
.aioseo-tools-robots-editor {
	.select-site {
		font-size: 16px;
		font-weight: bold;
		margin-bottom: 5px;
	}

	.aioseo-alert {
		margin-bottom: 20px;
	}

	.robots-background {
		position: relative;

		.loader-overlay {
			position: absolute;
			height: 100%;
			width: 100%;
			background: rgba(0, 0, 0, 0.3);
			z-index: 1;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.aioseo-settings-row:last-child {

			> .settings-name {
				margin-bottom: 16px;
			}
		}
	}

	.robots-table {
		border: 1px solid $input-border;
		border-radius: 3px;
		margin-bottom: 20px;

		.robots-user-agent {
			max-width: 180px;
		}

		.robots-rule {
			max-width: 200px;
			display: inline-flex;
			align-items: center;

			.aioseo-radio:not(:first-child) {
				margin-left: 20px;
			}
		}

		.robots-actions {
			max-width: 20px;

			.aioseo-tooltip {
				display: inline-block;
				margin: 0;
			}
		}

		.robots-header {
			height: 50px;
			display: flex;
			font-size: 14px;
			padding: 0 16px;
			align-items: center;
			border-bottom: 1px solid $input-border;

			> div {
				flex: 1 0 auto;
			}
		}

		.robots-rows {
			font-size: 14px;

			.robots-row {
				background-color: #fff;
				height: 72px;
				display: flex;
				align-items: center;
				padding: 0 16px;

				&:last-of-type {
					border-radius: 0 0 3px 3px;
				}

				&.even {
					background-color: $box-background;
				}

				> div {
					flex: 1 0 auto;
					padding-right: 16px;

					&:last-child {
						padding-right: 0;
					}
				}

				.robots-actions {
					svg.aioseo-trash {
						width: 20px;
						height: 20px;
						color: $placeholder-color;
						cursor: pointer;
						transition: color 0.1s ease;

						&:hover {
							color: $red;
						}
					}
				}
			}
		}
	}

	svg.aioseo-external,
	svg.aioseo-circle-plus {
		width: 14px;
		height: 14px;
		margin-right: 10px;
	}

	.physical-robots {
		.buttons {
			margin-top: 20px;

			.aioseo-button {
				margin-right: 10px;
			}
		}
	}
}
</style>