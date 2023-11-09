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
				<div class="settings-name">
					<div class="name small-margin">{{ strings.selectSite }}</div>
				</div>

				<div class="settings-content">
					<core-network-site-selector
						@selected-site="currentSite = $event"
						follow-selected-site
						show-network
					/>
				</div>
			</div>

			<div class="aioseo-settings-row">
				<core-alert v-if="'network' === this.currentSite?.blog_id">
					{{
						licenseStore.isUnlicensed || !license.hasCoreFeature('tools', 'network-tools-robots')
							? strings.networkAlertLite
							: strings.networkAlert
					}}
				</core-alert>

				<p class="description">
					{{ strings.description }}
				</p>

				<p class="description">
					{{ strings.description2 }}

					<span v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'robotsEditor', true)"/>
				</p>
			</div>

			<div
				v-if="rootStore.aioseo.data.robots.hasPhysicalRobots && showRobotsDetectedAlert"
				class="aioseo-settings-row"
			>
				<core-alert
					v-if="errors.importAndDeleteRobotsTxt || errors.deleteRobotsTxt"
					type="red"
				>
					{{ errors.importAndDeleteRobotsTxt || errors.deleteRobotsTxt }}
				</core-alert>

				<core-alert
					type="red"
					:show-close="!getOptions.enable"
					@close-alert="hideRobotsDetected"
				>
					<p class="description">
						{{ strings.physicalRobotsFound }}
					</p>

					<p class="buttons description">
						<base-button
							type="blue"
							size="medium"
							@click="onClickBtnImportAndDeleteRobotsTxt"
							:loading="loading.btnImportAndDeleteRobotsTxt"
						>
							{{ strings.importAndDelete }}
						</base-button>

						<base-button
							type="blue"
							size="medium"
							@click="onClickBtnDeleteRobotsTxt"
							:loading="loading.btnDeleteRobotsTxt"
						>
							{{ $constants.GLOBAL_STRINGS.delete }}
						</base-button>
					</p>
				</core-alert>
			</div>

			<div
				v-if="!rootStore.aioseo.data.robots.rewriteExists"
				class="aioseo-settings-row"
			>
				<core-alert
					type="red"
					v-html="missingRewriteRules"
				/>
			</div>

			<template v-if="isValidRobotsSite">
				<core-settings-row :name="$constants.GLOBAL_STRINGS.preview">
					<template #content>
						<base-button
							size="medium"
							type="blue"
							tag="a"
							:href="robotsTxtUrl"
							target="_blank"
						>
							<svg-external width="14"/>

							{{ strings.openRobotsTxt }}
						</base-button>
					</template>
				</core-settings-row>

				<core-settings-row
					:name="strings.enableCustomRobots"
					class="no-border no-margin"
				>
					<template #content>
						<base-toggle
							v-model="getOptions.enable"
						/>
					</template>
				</core-settings-row>

				<div class="aioseo-settings-row">
					<div class="settings-content">
						<div class="robots-editor-table">
							<div class="robots-editor-table__header">
								<div class="robots-editor-table__row">
									<div class="robots-editor-table__column">#</div>
									<div class="robots-editor-table__column">{{ strings.userAgent }}</div>
									<div class="robots-editor-table__column">{{ strings.directive }}</div>
									<div class="robots-editor-table__column">{{ $constants.GLOBAL_STRINGS.value }}</div>
									<div class="robots-editor-table__column"></div>
								</div>
							</div>

							<div class="robots-editor-table__body">
								<div
									v-for="(rule, index) in parsedDefaultRules"
									:key="index"
									class="robots-editor-table__row robots-editor-table__row--stripe"
								>
									<div class="robots-editor-table__column">
										{{ index + 1 }}
									</div>

									<div class="robots-editor-table__column">
										<base-input
											:modelValue="rule.userAgent"
											:disabled="true"
											size="medium"
										/>
									</div>

									<div class="robots-editor-table__column">
										<base-select
											:modelValue="directiveOptions.find(v => v.value === rule.directive)"
											:options="[]"
											:disabled="true"
											size="medium"
										/>
									</div>

									<div class="robots-editor-table__column">
										<base-input
											:modelValue="rule.fieldValue"
											:disabled="true"
											size="medium"
										/>
									</div>

									<div class="robots-editor-table__column"/>
								</div>

								<draggable
									handle=".aioseo-drag-wrapper"
									v-model="tableRules"
									:item-key="_uid.toString()"
									class="draggable-rules"
								>
									<template #item="{element:rule, index}">
										<div
											class="robots-editor-table__row robots-editor-table__row--stripe"
											:class="{'aioseo-error': hasTableRuleError(index + parsedDefaultRules.length + 1, rule)}"
										>
											<div class="robots-editor-table__column robots-editor-table__column--truncate">
												{{ index + parsedDefaultRules.length + 1 }}
											</div>

											<div class="robots-editor-table__column">
												<base-input
													v-model="rule.userAgent"
													:spellcheck="false"
													:disabled="!getOptions.enable"
													@change="updateRule(rule, 'userAgent', $event, index)"
													size="medium"
												/>
											</div>

											<div class="robots-editor-table__column">
												<base-select
													:modelValue="directiveOptions.find(v => v.value === rule.directive)"
													:options="directiveOptions"
													:disabled="!getOptions.enable"
													@update:modelValue="updateRule(rule, 'directive', $event.value, index)"
													size="medium"
												/>
											</div>

											<div class="robots-editor-table__column">
												<base-input
													v-model="rule.fieldValue"
													:spellcheck="false"
													:disabled="!getOptions.enable"
													@change="updateRule(rule, 'fieldValue', $event, index)"
													size="medium"
												/>
											</div>

											<div class="robots-editor-table__column robots-editor-table__column--actions">
												<a
													@click.prevent="deleteRow(index)"
													href="#"
													role="button"
													class="btn-delete-rule aioseo-outline"
													:title="strings.deleteRule"
													v-show="getOptions.enable"
												>
													<svg-trash width="20"/>
												</a>

												<a
													@click.prevent
													href="#"
													role="button"
													class="aioseo-drag-wrapper aioseo-outline"
													v-show="getOptions.enable"
												>
													<svg-drag width="20"/>
												</a>
											</div>

											<rule-errors
												:errors="getTableRuleErrors(index + parsedDefaultRules.length + 1, rule)"
												class="robots-editor-table__column robots-editor-table__column--rule-error"
											/>
										</div>
									</template>
								</draggable>
							</div>

							<div class="robots-editor-table__footer">
								<div class="buttons">
									<base-button
										@click.exact="addRow"
										:disabled="!getOptions.enable"
										class="btn-add-rule"
										type="black"
										size="small"
									>
										<svg-circle-plus width="14"/>

										{{ strings.addRule }}
									</base-button>

									<base-button
										@click.prevent="showImportModal = true"
										:disabled="!getOptions.enable"
										type="black"
										size="small"
									>
										<svg-upload width="14"/>

										{{ $constants.GLOBAL_STRINGS.import }}
									</base-button>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="aioseo-settings-row aioseo-settings-row--preview-robots no-margin">
					<div class="settings-name">
						<div class="name">
							{{ strings.customRobotsPreview }}

							<div
								v-if="robotsPreviewErrorLabel"
								class="aioseo-error"
							>
								<svg-ellipse width="8"/>

								<span>{{ robotsPreviewErrorLabel }}</span>
							</div>
						</div>
					</div>

					<div class="settings-content">
						<base-editor
							:modelValue="inputCustomRobotsTxtPreview"
							:line-numbers="true"
							disabled
							force-updates
							monospace
							ref="input-custom-robots-txt-preview"
						/>
					</div>
				</div>
			</template>

			<template v-else>
				<core-alert v-html="subdirectoryAlert"/>
			</template>

			<core-modal
				v-if="showImportModal"
				@close="showImportModal = false"
			>
				<template #headerTitle>
					{{ strings.importRobots }}
				</template>

				<template #body>
					<div
						v-if="errors.importRobotsTxt"
						class="aioseo-settings-row no-margin no-border"
					>
						<core-alert
							type="red"
							show-close
							@close-alert="errors.importRobotsTxt = null"
						>
							{{ errors.importRobotsTxt }}
						</core-alert>
					</div>

					<div
						class="aioseo-settings-row aioseo-settings-row--or"
						:data-or="$constants.GLOBAL_STRINGS.or"
					>
						<div class="settings-name">
							<div class="name small-margin">
								{{ strings.importFromUrl }}
							</div>
						</div>

						<div class="settings-content settings-content--gap">
							<base-input
								:modelValue="inputImportRobotsTxtFromUrl"
								@update:modelValue="v => onChangeInputImportRobotsTxtFromUrl(v)"
								:placeholder="strings.pasteUrl"
								:disabled="!!inputPasteRobotsTxtText"
								type="url"
								size="medium"
							/>

							<core-alert
								v-if="errors.importRobotsTxtFromUrl"
								type="red"
								size="small"
							>
								{{ errors.importRobotsTxtFromUrl }}
							</core-alert>
						</div>
					</div>

					<div class="aioseo-settings-row">
						<div class="settings-name">
							<div class="name small-margin">{{ strings.pasteRobotsText }}</div>
						</div>

						<div class="settings-content settings-content--gap">
							<base-editor
								:modelValue="inputPasteRobotsTxtText"
								@update:modelValue="v => onChangeInputPasteRobotsTxtText(v)"
								:line-numbers="true"
								:minimum-line-numbers="10"
								:disabled="!!inputImportRobotsTxtFromUrl"
								:spellcheck="false"
								monospace
							/>

							<core-alert
								v-if="errors.pasteRobotsTxtText"
								type="red"
								size="small"
							>
								{{ errors.pasteRobotsTxtText }}
							</core-alert>
						</div>
					</div>
				</template>

				<template #footer>
					<div class="buttons">
						<base-button
							@click.exact="showImportModal = false"
							type="gray"
							size="medium"
						>
							{{ $constants.GLOBAL_STRINGS.cancel }}
						</base-button>

						<base-button
							@click.exact="onClickBtnImportRobotsTxt(inputImportRobotsTxtFromUrl ? 'url' : 'text')"
							:loading="loading.btnImportRobotsTxt"
							:disabled="!!btnImportRobotsTxtDisabled"
							type="blue"
							size="medium"
						>
							{{ $constants.GLOBAL_STRINGS.import }}
						</base-button>
					</div>
				</template>
			</core-modal>

			<div
				class="loader-overlay"
				v-if="loading.cardOverlay"
			>
				<core-loader/>
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
import { arrayColumn, arrayUnique } from '@/vue/utils/helpers'
import { groupRulesByUserAgent, stringifyRuleset, validateRuleset } from '@/vue/utils/robots'
import { Network } from '@/vue/mixins/Network'
import { SaveChanges } from '@/vue/mixins/SaveChanges'
import BaseButton from '@/vue/components/common/base/Button'
import BaseEditor from '@/vue/components/common/base/Editor'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CoreLoader from '@/vue/components/common/core/Loader'
import CoreModal from '@/vue/components/common/core/modal/Index'
import CoreNetworkSiteSelector from '@/vue/components/common/core/NetworkSiteSelector'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import Draggable from 'vuedraggable'
import RuleErrors from './partials/robots-editor/RuleErrors'
import SvgCirclePlus from '@/vue/components/common/svg/circle/Plus'
import SvgEllipse from '@/vue/components/common/svg/Ellipse'
import SvgExternal from '@/vue/components/common/svg/External'
import SvgTrash from '@/vue/components/common/svg/Trash'
import SvgUpload from '@/vue/components/common/svg/Upload'
import SvgDrag from '@/vue/components/common/svg/Drag'

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
		SvgDrag,
		BaseButton,
		BaseEditor,
		CoreAlert,
		CoreCard,
		CoreLoader,
		CoreModal,
		CoreNetworkSiteSelector,
		CoreSettingsRow,
		Draggable,
		RuleErrors,
		SvgCirclePlus,
		SvgEllipse,
		SvgExternal,
		SvgTrash,
		SvgUpload
	},
	mixins : [ Network, SaveChanges ],
	data () {
		return {
			currentSite      : {},
			defaultRules     : this.rootStore.aioseo.data.robots?.defaultRules || {},
			directiveOptions : [
				{ value: 'allow', label: 'Allow' },
				{ value: 'disallow', label: 'Disallow' },
				{ value: 'clean-param', label: 'Clean-param' },
				{ value: 'crawl-delay', label: 'Crawl-delay' }
			],
			errors : {
				deleteRobotsTxt          : null,
				importAndDeleteRobotsTxt : null,
				importRobotsTxt          : null,
				importRobotsTxtFromUrl   : null,
				pasteRobotsTxtText       : null,
				tableRule                : []
			},
			forceRobotsDetectedAlert    : false,
			inputImportRobotsTxtFromUrl : '',
			inputPasteRobotsTxtText     : '',
			license,
			loading                     : {
				btnDeleteRobotsTxt          : false,
				btnImportAndDeleteRobotsTxt : false,
				btnImportRobotsTxt          : false,
				cardOverlay                 : false
			},
			showImportModal : false,
			strings         : {
				addRule             : this.$t.__('Add Rule', this.$td),
				allow               : this.$t.__('Allow', this.$td),
				customRobotsPreview : this.$t.__('Custom Robots.txt Preview', this.$td),
				deleteRule          : this.$t.__('Delete Rule', this.$td),
				description         : this.$t.sprintf(
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
				directive           : this.$t.__('Directive', this.$td),
				disallow            : this.$t.__('Disallow', this.$td),
				enableCustomRobots  : this.$t.__('Enable Custom Robots.txt', this.$td),
				importAndDelete     : this.$t.__('Import and Delete', this.$td),
				importFromUrl       : this.$t.__('Import from URL', this.$td),
				importRobots        : this.$t.__('Import Robots.txt', this.$td),
				networkAlert        : this.$t.__('These custom robots.txt rules will apply globally to your entire network. To adjust the robots.txt rules for an individual site, please choose it in the list above.', this.$td),
				networkAlertLite    : this.$t.__('These custom robots.txt rules will apply globally to your entire network. To adjust the robots.txt rules for an individual site, please visit the dashboard for that site directly and update the settings there.', this.$td),
				invalidRobotsTxtUrl : this.$t.__('Invalid robots.txt URL.', this.$td),
				openRobotsTxt       : this.$t.__('Open Robots.txt', this.$td),
				pasteRobotsText     : this.$t.__('Paste Robots.txt text', this.$td),
				userAgentNotFound   : this.$t.__('No User-agent found in the content beginning.', this.$td),
				// Translators: This domain is just a placeholder, please translate "any-domain" to your language so the user understands that they can add any domain here.
				pasteUrl            : this.$t.__('https://any-domain.com/robots.txt', this.$td),
				physicalRobotsFound : this.$t.sprintf(
					// Translators: 1 - The plugin short name ("AIOSEO"), 2 - The plugin short name ("AIOSEO").
					this.$t.__('%1$s has detected a physical robots.txt file in the root folder of your WordPress installation. We recommend removing this file as it could cause conflicts with WordPress\' dynamically generated one. %2$s can import this file and delete it, or you can simply delete it.', this.$td),
					import.meta.env.VITE_SHORT_NAME,
					import.meta.env.VITE_SHORT_NAME
				),
				robotsEditor : this.$t.__('Robots.txt Editor', this.$td),
				selectSite   : this.$t.__('Select Site', this.$td),
				userAgent    : this.$t.__('User Agent', this.$td)
			}
		}
	},
	watch : {
		'networkStore.networkRobots' : {
			deep : true,
			handler () {
				if ('network' === this.currentSite?.blog_id) {
					this.optionsStore.networkOptions.tools.robots.rules = this.networkStore.networkRobots.rules
				} else {
					this.optionsStore.options.tools.robots.rules = this.networkStore.networkRobots.rules
				}

				this.$nextTick(() => {
					this.validateRules()
				})
			}
		},
		currentSite (_newVal, oldVal) {
			if (oldVal.blog_id) {
				this.processFetchSiteRobots()
			}
		},
		'getOptions.enable' () {
			this.validateRules()
			this.maybeForceRobotsDetectedAlert()
		}
	},
	computed : {
		btnImportRobotsTxtDisabled () {
			if (!this.inputImportRobotsTxtFromUrl && !this.inputPasteRobotsTxtText) {
				return true
			}

			return this.errors.importRobotsTxtFromUrl || this.errors.pasteRobotsTxtText
		},
		getOptions () {
			return 'network' === this.currentSite?.blog_id ? this.networkStore.getNetworkRobots : this.optionsStore.options.tools.robots
		},
		inputCustomRobotsTxtPreview () {
			const networkRules = this.isNetworkSite && this.optionsStore.networkOptions.tools.robots.enable ? groupRulesByUserAgent(this.networkStore.getNetworkRobots.rules) : {}
			const customRules = groupRulesByUserAgent(this.networkStore.networkRobots.rules)
			const sitemapUrls = '\r\n' + this.rootStore.aioseo.data.robots.sitemapUrls.filter(url => 0 < url.length).join('\r\n')

			let output = this.getOptions.enable
				? this.mergeRuleset(this.defaultRules, this.mergeRuleset(networkRules, customRules), true)
				: this.mergeRuleset(this.defaultRules, networkRules)
			output = stringifyRuleset(output) + sitemapUrls

			return output.replace(/<[^>]*>/g, '')
		},
		isNetworkSite () {
			return this.rootStore.aioseo.data.isMultisite && 'network' !== this.currentSite?.blog_id
		},
		isValidRobotsSite () {
			const parseRobotsTxtUrl = new URL(this.robotsTxtUrl)

			// The robots.txt file must be located at the root of the website host to which it applies.
			if (!parseRobotsTxtUrl.pathname.match(/^\/robots.txt\/?/)) {
				return false
			}

			return this.rootStore.aioseo.data.subdomain || 'network' === this.currentSite?.blog_id || this.isMainSite(this.currentSite.domain, this.currentSite.path) || (!this.rootStore.aioseo.data.isNetworkAdmin && this.rootStore.aioseo.data.mainSite)
		},
		missingRewriteRules () {
			const string1 = this.$t.__('It looks like you are missing the proper rewrite rules for the robots.txt file.', this.$td)
			let string2 = ''
			if (this.rootStore.aioseo.data.server.apache) {
				string2 = this.$t.sprintf(
					// Translators: 1 - Opening link tag, 2 - Closing link tag.
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
		},
		parsedCustomRules () {
			const parsed = []
			for (const rule of this.networkStore.networkRobots.rules.values()) {
				const r = JSON.parse(rule)
				parsed.push({
					userAgent    : r.userAgent,
					directive    : r.directive,
					fieldValue   : r.fieldValue,
					default      : false,
					networkLevel : false
				})
			}
			return parsed
		},
		parsedDefaultRules () {
			const parsed = []
			Object.keys(this.defaultRules).forEach(userAgent => {
				for (const rule of this.defaultRules[userAgent].values()) {
					const [ directive, value ] = rule.split(':').map(v => v.trim())
					parsed.push({
						userAgent,
						directive,
						fieldValue   : value,
						default      : true,
						networkLevel : false
					})
				}
			})

			return parsed
		},
		parsedNetworkRules () {
			const networkRules = this.isNetworkSite && this.optionsStore.networkOptions.tools.robots.enable ? this.networkStore.getNetworkRobots.rules : {}
			const parsed = []
			if (Object.keys(networkRules).length) {
				for (const rule of networkRules.values()) {
					const r = JSON.parse(rule)
					parsed.push({
						userAgent    : r.userAgent,
						directive    : r.directive,
						fieldValue   : r.fieldValue,
						default      : false,
						networkLevel : true
					})
				}
			}
			return parsed
		},
		robotsPreviewErrorLabel () {
			const redErrors = this.errors.tableRule.length ? this.errors.tableRule.filter(e => 'red' === e.type) : []
			if (!redErrors.length) {
				return ''
			}

			const previewIndexes = arrayColumn(redErrors, 'previewIndex')
			const previewIndexesCount = arrayUnique(previewIndexes).length
			return this.$t.sprintf(
				// Translators: 1 - The amount of errors.
				this.$t._n('%1$s Error', '%1$s Errors', previewIndexesCount, this.$td),
				previewIndexesCount
			)
		},
		robotsTxtUrl () {
			if ('network' !== this.currentSite?.blog_id && (this.currentSite?.domain || null)) {
				return `${this.rootStore.aioseo.data.isSsl ? 'https://' : 'http://'}${this.currentSite.domain}${this.currentSite.path}robots.txt`
			}

			return this.rootStore.aioseo.urls.robotsTxtUrl
		},
		subdirectoryAlert () {
			if (this.isNetworkSite) {
				return this.$t.sprintf(
					// Translators: 1 - The url to the main site.
					this.$t.__('This site is running in a sub-directory of your main site located at %1$s. Your robots.txt file should only appear in the root directory of that site.', this.$td),
					'<a href="' + this.rootStore.aioseo.urls.mainSiteUrl + '" target="_blank"><strong>' + this.rootStore.aioseo.urls.mainSiteUrl + '</strong></a>'
				)
			}

			return this.$t.__('This site runs in a sub-directory. The robots.txt file must be located at the root of the website host to which it applies.', this.$td)
		},
		showRobotsDetectedAlert () {
			return this.getOptions.robotsDetected || this.forceRobotsDetectedAlert
		},
		tableRules : {
			get () {
				return this.networkStore.networkRobots.rules.map(rule => JSON.parse(rule))
			},
			set (newTableRules) {
				const rawRules = []
				newTableRules.forEach(parsedRule => {
					rawRules.push(JSON.stringify({
						userAgent  : parsedRule.userAgent,
						directive  : parsedRule.directive,
						fieldValue : parsedRule.fieldValue
					}))
				})
				this.networkStore.networkRobots.rules = rawRules
			}
		}
	},
	methods : {
		addRow () {
			this.networkStore.networkRobots.rules.push(JSON.stringify({
				userAgent  : null,
				directive  : 'allow',
				fieldValue : null
			}))

			this.$nextTick(() => {
				document.querySelector('.robots-editor-table__body .robots-editor-table__row:last-child input').focus()
			})
		},
		deleteRow (index) {
			this.networkStore.networkRobots.rules.splice(index, 1)
		},
		getTableIndexedRuleset () {
			const ruleset = []
			const tableIndexedRules = []
			const mergedParsedRules = [ ...this.parsedDefaultRules, ...this.parsedNetworkRules, ...this.parsedCustomRules ]

			let networkTableIndex = this.parsedDefaultRules.length,
				customTableIndex = 0
			// Append table index.
			mergedParsedRules.forEach(rule => {
				rule.tableIndex = rule.networkLevel ? ++networkTableIndex : ++customTableIndex
				tableIndexedRules.push(rule)
			})

			// Group by User-agent.
			tableIndexedRules.forEach(rule => {
				if (!ruleset[rule.userAgent]) {
					ruleset[rule.userAgent] = [ rule ]

					return
				}

				ruleset[rule.userAgent].push(rule)
			})

			return ruleset
		},
		getTableRuleErrors (tableIndex, rule) {
			if (this.errors.tableRule.length) {
				return this.errors.tableRule.filter(e => {
					return e.hash === `${tableIndex}${rule.userAgent}${rule.directive}${rule.fieldValue}`
				})
			}

			return []
		},
		hasTableRuleError (tableIndex, rule) {
			return this.errors.tableRule.find((e) => {
				// The `foundTableIndex` can't come from the network level, otherwise the wrong table rule might be red-highlighted.
				const foundTableIndex = tableIndex === (e.duplicateIndex || e.equivalentIndex || e.conflictingIndex) && !e.isNetworkIndex
				const foundHash = e.hash === `${tableIndex}${rule.userAgent}${rule.directive}${rule.fieldValue}`

				return foundTableIndex || foundHash
			}) || false
		},
		hideRobotsDetected () {
			this.getOptions.robotsDetected = false

			this.optionsStore.saveChanges()
		},
		maybeForceRobotsDetectedAlert () {
			if (!this.getOptions.enable) {
				this.forceRobotsDetectedAlert = false
			}

			if (this.getOptions.enable && this.rootStore.aioseo.data.robots.hasPhysicalRobots) {
				this.forceRobotsDetectedAlert = true
			}
		},
		mergeRuleset (firstRules, secondRules, allowOverride = false) {
			const merged = { ...firstRules }
			Object.keys(secondRules).forEach(userAgent => {
				if (!(userAgent in merged)) {
					merged[userAgent] = secondRules[userAgent]

					return
				}

				for (const rule of secondRules[userAgent].values()) {
					const [ directive, value ] = rule.split(':').map(v => v.trim())
					if (directive.match(/disallow|allow/i)) {
						const otherDirective = 'disallow' === directive ? 'allow' : 'disallow'
						const conflictingIndex = merged[userAgent].indexOf(`${otherDirective}: ${value}`)
						if (-1 !== conflictingIndex) {
							if (allowOverride) {
								merged[userAgent] = merged[userAgent].filter((_v, i) => i !== conflictingIndex)
							} else {
								secondRules[userAgent] = secondRules[userAgent].filter((v) => v !== `${directive}: ${value}`)
							}
						}
					}
				}

				merged[userAgent] = [
					...merged[userAgent],
					...secondRules[userAgent]
				]
			})

			return merged
		},
		onChangeInputImportRobotsTxtFromUrl (value) {
			this.inputImportRobotsTxtFromUrl = value
			this.errors.importRobotsTxtFromUrl = null
			if (value && !value.match(/^https?:\/\/.{2,}\..{2,}\/robots\.txt$/)) {
				this.errors.importRobotsTxtFromUrl = this.strings.invalidRobotsTxtUrl
			}
		},
		onChangeInputPasteRobotsTxtText (value) {
			this.inputPasteRobotsTxtText = value.replace(/#[^\n\r]*/g, '')
			this.errors.pasteRobotsTxtText = null
			if (this.inputPasteRobotsTxtText && !this.inputPasteRobotsTxtText.match(/^\s*user-agent:\s*./gi)) {
				this.errors.pasteRobotsTxtText = this.strings.userAgentNotFound
			}
		},
		onClickBtnDeleteRobotsTxt () {
			this.loading.btnDeleteRobotsTxt = true

			this.notificationsStore.processButtonAction('tools/delete-robots-txt')
				.then(() => (window.location.reload()))
				.catch(error => {
					this.loading.btnDeleteRobotsTxt = false
					this.errors.deleteRobotsTxt = error?.response?.body?.message || null
				})
		},
		onClickBtnImportAndDeleteRobotsTxt () {
			this.loading.btnImportAndDeleteRobotsTxt = true

			this.processImportRobotsTxt('static')
				.then(() => {
					window.location.reload()
				})
				.catch(error => {
					this.loading.btnImportAndDeleteRobotsTxt = false
					this.errors.importAndDeleteRobotsTxt = error?.response?.body?.message || null
				})
		},
		onClickBtnImportRobotsTxt (source) {
			this.loading.btnImportRobotsTxt = true

			this.processImportRobotsTxt(source)
				.then(() => (window.location.reload()))
				.catch(error => {
					this.loading.btnImportRobotsTxt = false
					this.errors.importRobotsTxt = error?.response?.body?.message || null
				})
		},
		processFetchSiteRobots () {
			this.loading.cardOverlay = true

			this.networkStore.fetchSiteRobots(this.currentSite.blog_id)
				.then(() => (this.loading.cardOverlay = false))
		},
		processImportRobotsTxt (source) {
			return this.networkStore.importRobotsTxt({
				source,
				url          : this.inputImportRobotsTxtFromUrl,
				text         : this.inputPasteRobotsTxtText,
				networkLevel : this.rootStore.aioseo.data.isNetworkAdmin,
				blogId       : this.currentSite?.blog_id || null
			})
		},
		sanitizeDirectiveValue (rule, type, value) {
			// Percent-encoded characters are stripped from our option values, so we decode.
			value = decodeURIComponent(value.trim())
			if (!value) {
				return value
			}

			value = value.replace(/[><]/, '')

			if ('userAgent' === type) {
				value = value.replace(/([^a-z0-9\-_*,.\s])/gi, '')
				value = value.replace(/\s+/g, ' ')
			}

			if ('fieldValue' === type && rule.directive.match(/allow|disallow/)) {
				value = 0 === value.indexOf('*') && 1 < value.length ? value : '/' + value.replace(/(^\/+)/, '')
			}

			return value
		},
		async updateRule (rule, type, value, index) {
			// Make sure sanitized values always show in the input field by tracking all rule changes in the store.
			this.networkStore.networkRobots.rules.splice(index, 1, JSON.stringify(rule))

			await this.$nextTick()

			rule[type] = this.sanitizeDirectiveValue(rule, type, value)

			this.networkStore.networkRobots.rules.splice(index, 1, JSON.stringify(rule))
		},
		validateRules () {
			if (!this.getOptions.enable) {
				this.errors.tableRule = []

				return false
			}

			try {
				validateRuleset(this.getTableIndexedRuleset())

				this.errors.tableRule = []
			} catch (errors) {
				this.errors.tableRule = errors

				this.$nextTick(() => {
					const redErrors = this.errors.tableRule.length ? this.errors.tableRule.filter(e => 'red' === e.type) : []
					const robotsPreviewEditor = this.$refs['input-custom-robots-txt-preview']?.$el.querySelector('.ql-editor') || null
					if (!robotsPreviewEditor) {
						return false
					}

					const robotsPreviewLines = robotsPreviewEditor.querySelectorAll('p')
					const previewIndexes = arrayColumn(redErrors, 'previewIndex')
					const sourcePreviewIndexes = arrayColumn(redErrors, 'sourcePreviewIndex')
					const searchIndexes = [ ...new Set([ ...previewIndexes, ...sourcePreviewIndexes ]) ]
					for (const index of searchIndexes) {
						if (robotsPreviewLines[index - 1]) {
							robotsPreviewLines[index - 1].classList.add('has-error')
						}
					}
				})
			}
		}
	},
	mounted () {
		this.networkStore.networkRobots.rules = 'network' === this.currentSite?.blog_id
			? this.networkStore.getNetworkRobots.rules
			: this.optionsStore.options.tools.robots.rules

		this.validateRules()
		this.maybeForceRobotsDetectedAlert()
	}
}
</script>

<style lang="scss">
.aioseo-tools-robots-editor {
	.buttons {
		display: flex;
		flex-wrap: wrap;
		gap: 12px;
	}

	.aioseo-alert,
	.description {
		color: $black;
		margin: 0;

		+ .aioseo-alert,
		+ .description {
			margin-top: 12px;
		}
	}

	.robots-editor-table {
		border-radius: 4px;
		border: 1px solid $input-border;
		color: $black;

		&__header {
			border-bottom: 1px solid $input-border;
		}

		&__footer {
			border-top: 1px solid $input-border;
			padding: 9px 16px;
		}

		&__row {
			align-items: center;
			display: grid;
			gap: 16px;
			grid-template-columns: 1fr 6fr 6fr 12fr 1fr auto;
			padding: 16px;

			&--stripe:nth-child(odd) {
				background-color: $background;
			}
		}

		&__column {
			&--rule-error {
				grid-column: 2/span 5;
				padding-right: 16px;
			}

			&--truncate {
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
			}

			&--actions {
				align-items: center;
				display: flex;
				justify-content: space-between;
				line-height: 1;

				.btn-delete-rule {
					cursor: pointer;
					height: 20px;
					width: 20px;

					svg {
						color: $placeholder-color;

						&:hover {
							color: $red;
						}
					}
				}
			}
		}
	}

	.aioseo-settings-row {
		&--preview-robots {
			.settings-name {
				margin-bottom: 16px;

				.name {
					.aioseo-error {
						align-items: center;
						color: $red;
						display: inline-flex;
						line-height: normal;
						gap: 8px;
						margin-left: 16px;
					}
				}
			}

			.ql-editor {
				.has-error {
					background-color: #FECACA;
					display: table;
				}
			}
		}

		&--or {
			margin-bottom: 35px;
			padding-bottom: 35px;
			position: relative;

			&:before {
				align-items: center;
				background-color: $input-border;
				border-radius: 50%;
				content: attr(data-or);
				display: inline-flex;
				font-size: 12px;
				font-weight: 700;
				height: 30px;
				justify-content: center;
				left: 50%;
				line-height: 30px;
				margin-bottom: 35px;
				position: absolute;
				text-transform: uppercase;
				top: calc(100% - 15px);
				transform: translateX(-50%);
				width: 30px;
			}
		}

		.settings-content {
			&--gap {
				display: grid;
				gap: 10px;
			}
		}
	}

	.aioseo-modal {
		.modal-header {
			padding-left: 20px;
		}

		.modal-container {
			height: auto;
			overflow: revert;

			.modal-body {
				max-height: 70vh;
				padding: 20px;
			}

			&__footer {
				display: flex;
				justify-content: end;
				padding: 12px 20px;
			}
		}
	}

	.aioseo-drag-wrapper {
		cursor: move;
		width: 20px;
		height: 20px;

		svg.aioseo-drag {
			color: $placeholder-color;
		}
	}

	.aioseo-outline {
		display: inline-block;
		outline: revert;
		outline-color: $blue;
	}

	svg.aioseo-upload,
	svg.aioseo-external,
	svg.aioseo-circle-plus {
		width: 14px;
		height: 14px;
		margin-right: 10px;
	}

	.loader-overlay {
		background: rgba(0, 0, 0, 0.3);
		display: flex;
		height: 100%;
		justify-content: center;
		left: 0;
		padding: 50px;
		position: absolute;
		top: 0;
		width: 100%;
	}
}
</style>