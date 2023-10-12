<template>
	<div class="aioseo-advanced">
		<core-card
			slug="advanced"
			:header-text="strings.advanced"
		>
			<core-settings-row
				:name="strings.truSeo"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.advanced.truSeo"
					/>

					<div class="aioseo-description">
						{{ strings.truSeoDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.headlineAnalyzer"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.advanced.headlineAnalyzer"
						:disabled="versionCompare(rootStore.aioseo.wpVersion, '5.2', '<')"
					/>

					<div class="aioseo-description">
						{{ strings.headlineAnalyzerDescription }}
					</div>

					<core-alert
						class="warning"
						v-if="versionCompare(rootStore.aioseo.wpVersion, '5.2', '<')"
						type="yellow"
					>
						<div v-html="strings.headlineAnalyzerWarning"/>
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.postTypeColumns"
			>
				<template #content>
					<base-checkbox
						size="medium"
						v-model="optionsStore.options.advanced.postTypes.all"
					>
						{{ strings.includeAllPostTypes }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.advanced.postTypes.all"
						:options="optionsStore.options.advanced"
						type="postTypes"
					/>

					<div class="aioseo-description">
						{{ strings.selectPostTypes }}

						<span
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'selectPostTypesColumns', true)"
						/>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row>
				<template #name>
					{{ strings.taxonomyColumns }}
					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>
				<template #content>
					<base-checkbox
						v-if="licenseStore.isUnlicensed"
						disabled
						size="medium"
						:modelValue="true"
					>
						{{ strings.includeAllTaxonomies }}
					</base-checkbox>

					<base-checkbox
						v-if="!licenseStore.isUnlicensed"
						size="medium"
						v-model="optionsStore.options.advanced.taxonomies.all"
					>
						{{ strings.includeAllTaxonomies }}
					</base-checkbox>

					<core-post-type-options
						v-if="!optionsStore.options.advanced.taxonomies.all && !licenseStore.isUnlicensed"
						:options="optionsStore.options.advanced"
						type="taxonomies"
					/>

					<div class="aioseo-description">
						{{ strings.selectTaxonomies }}

						<span
							v-html="$links.getDocLink($constants.GLOBAL_STRINGS.learnMore, 'selectTaxonomiesColumns', true)"
						/>
					</div>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.taxonomyColumnsUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row>
				<template #name>
					{{ strings.adminBarMenu }}
					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>

				<template #content>
					<base-radio-toggle
						:disabled="licenseStore.isUnlicensed"
						v-model="adminBarMenu"
						name="adminBarMenu"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.hide, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.show, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.adminBarMenuDescription }}
					</div>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.adminBarMenuUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row>
				<template #name>
					{{ strings.dashboardWidgets }}
					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>
				<template #content>
					<grid-row>
						<grid-column
							v-for="(widget, index) in widgets"
							:key="index"
						>
							<base-checkbox
								size="medium"
								:disabled="licenseStore.isUnlicensed"
								:modelValue="isDashboardWidgetChecked(widget)"
								@update:modelValue="value => updateDashboardWidgets(value, widget)"
							>
								{{ widget.label }}
								<core-tooltip>
									<svg-circle-question-mark />

									<template #tooltip>
										{{ widget.tooltip }}
									</template>
								</core-tooltip>
							</base-checkbox>
						</grid-column>
					</grid-row>

					<div class="aioseo-description">
						{{ strings.dashboardWidgetsDescription }}
					</div>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.dashboardWidgetsUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.announcements"
			>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.advanced.announcements"
						name="announcements"
						:options="[
							{ label: $constants.GLOBAL_STRINGS.hide, value: false, activeClass: 'dark' },
							{ label: $constants.GLOBAL_STRINGS.show, value: true }
						]"
					/>

					<div class="aioseo-description">
						{{ strings.announcementsDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row>
				<template #name>
					{{ strings.automaticUpdates }}
				</template>
				<template #content>
					<base-radio-toggle
						v-model="optionsStore.options.advanced.autoUpdates"
						name="autoUpdates"
						:options="[
							{ label: strings.all, value: 'all' },
							{ label: strings.minor, value: 'minor' },
							{ label: strings.none, value: 'none', activeClass: 'dark' }
						]"
					/>

					<div class="aioseo-description">
						<span v-if="'all' === optionsStore.options.advanced.autoUpdates">{{ strings.allDescription }}</span>
						<span v-if="'minor' === optionsStore.options.advanced.autoUpdates">{{ strings.minorDescription }}</span>
						<span v-if="'none' === optionsStore.options.advanced.autoUpdates">{{ strings.noneDescription }}</span>
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				v-if="!$isPro"
			>
				<template #name>
					{{ strings.usageTracking }}
					<core-tooltip>
						<svg-circle-question-mark />

						<template #tooltip>
							<div v-html="strings.usageTrackingTooltip" />
						</template>
					</core-tooltip>
				</template>

				<template #content>
					<base-toggle
						v-model="optionsStore.options.advanced.usageTracking"
					/>

					<div class="aioseo-description">
						{{ strings.usageTrackingDescription }}
					</div>
				</template>
			</core-settings-row>

			<core-settings-row
				id="aioseo-open-ai-api-key"
				:name="strings.openAiKey"
			>
				<template #name>
					{{ strings.openAiKey }}
					<core-pro-badge
						v-if="licenseStore.isUnlicensed"
					/>
				</template>

				<template #content>
					<base-input
						class="openAiKey"
						type="text"
						size="medium"
						v-model="optionsStore.options.advanced.openAiKey"
						:disabled="licenseStore.isUnlicensed"
						@blur="validateOpenAiKey"
					/>

					<div
						class="aioseo-description"
						v-html="strings.openAiKeyDescription"
					/>

					<core-alert
						class="inline-upsell"
						v-if="!licenseStore.isUnlicensed && optionsStore.options.advanced.openAiKey && openAiKeyInvalid"
						type="red"
					>
						<div>{{strings.openAiKeyInvalid}}</div>
					</core-alert>

					<core-alert
						class="inline-upsell"
						v-if="licenseStore.isUnlicensed"
						type="blue"
					>
						<div v-html="strings.openAiKeyUpsell" />
					</core-alert>
				</template>
			</core-settings-row>

			<core-settings-row
				:name="strings.uninstallAioseo"
			>
				<template #content>
					<base-toggle
						v-model="optionsStore.options.advanced.uninstall"
					/>

					<div class="aioseo-description">
						{{ strings.uninstallAioseoDescription }}
					</div>
				</template>
			</core-settings-row>
		</core-card>
	</div>
</template>

<script>
import {
	useLicenseStore,
	useOptionsStore,
	useRootStore
} from '@/vue/stores'

import { versionCompare } from '@/vue/utils/helpers'

import BaseCheckbox from '@/vue/components/common/base/Checkbox'
import BaseRadioToggle from '@/vue/components/common/base/RadioToggle'
import CoreAlert from '@/vue/components/common/core/alert/Index'
import CoreCard from '@/vue/components/common/core/Card'
import CorePostTypeOptions from '@/vue/components/common/core/PostTypeOptions'
import CoreProBadge from '@/vue/components/common/core/ProBadge'
import CoreSettingsRow from '@/vue/components/common/core/SettingsRow'
import CoreTooltip from '@/vue/components/common/core/Tooltip'
import GridColumn from '@/vue/components/common/grid/Column'
import GridRow from '@/vue/components/common/grid/Row'
import SvgCircleQuestionMark from '@/vue/components/common/svg/circle/QuestionMark'

export default {
	setup () {
		return {
			licenseStore : useLicenseStore(),
			optionsStore : useOptionsStore(),
			rootStore    : useRootStore()
		}
	},
	components : {
		BaseCheckbox,
		BaseRadioToggle,
		CoreAlert,
		CoreCard,
		CorePostTypeOptions,
		CoreProBadge,
		CoreSettingsRow,
		CoreTooltip,
		GridColumn,
		GridRow,
		SvgCircleQuestionMark
	},
	data () {
		return {
			openAiKeyInvalid : false,
			strings          : {
				advanced                    : this.$t.__('Advanced Settings', this.$td),
				truSeo                      : this.$t.__('TruSEO Score & Content', this.$td),
				truSeoDescription           : this.$t.__('Enable our TruSEO score to help you optimize your content for maximum traffic.', this.$td),
				headlineAnalyzer            : this.$t.__('Headline Analyzer', this.$td),
				headlineAnalyzerDescription : this.$t.__('Enable our Headline Analyzer to help you write irresistible headlines and rank better in search results.', this.$td),
				seoAnalysis                 : this.$t.__('SEO Analysis', this.$td),
				postTypeColumns             : this.$t.__('Post Type Columns', this.$td),
				includeAllPostTypes         : this.$t.__('Include All Post Types', this.$td),
				selectPostTypes             : this.$t.sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					this.$t.__('Select which Post Types you want to use the %1$s columns with.', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				usageTracking           : this.$t.__('Usage Tracking', this.$td),
				adminBarMenu            : this.$t.__('Admin Bar Menu', this.$td),
				adminBarMenuDescription : this.$t.sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					this.$t.__('This adds %1$s to the admin toolbar for easy access to your SEO settings.', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				dashboardWidgets            : this.$t.__('Dashboard Widgets', this.$td),
				dashboardWidgetsDescription : this.$t.sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					this.$t.__('Select which %1$s widgets to display on the dashboard.', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				announcements            : this.$t.__('Announcements', this.$td),
				announcementsDescription : this.$t.__('This allows you to hide plugin announcements and update details in the Notification Center.', this.$td),
				automaticUpdates         : this.$t.__('Automatic Updates', this.$td),
				all                      : this.$t.__('All (recommended)', this.$td),
				allDescription           : this.$t.__('You are getting the latest features, bugfixes, and security updates as they are released.', this.$td),
				minor                    : this.$t.__('Minor Only', this.$td),
				minorDescription         : this.$t.__('You are getting bugfixes and security updates, but not major features.', this.$td),
				none                     : this.$t.__('None', this.$td),
				noneDescription          : this.$t.__('You will need to manually update everything.', this.$td),
				usageTrackingDescription : this.$t.__('By allowing us to track usage data we can better help you as we will know which WordPress configurations, themes and plugins we should test.', this.$td),
				usageTrackingTooltip     : this.$t.sprintf(
					// Translators: 1 - Opening HTML link and bold tag, 2 - Closing HTML link and bold tag.
					this.$t.__('Complete documentation on usage tracking is available %1$shere%2$s.', this.$td),
					this.$t.sprintf(
						'<strong><a href="%1$s" target="_blank">',
						this.$links.getDocUrl('usageTracking')
					),
					'</a></strong>'
				),
				adminBarMenuUpsell : this.$t.sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					this.$t.__('Admin Bar Menu is a %1$s feature. %2$s', this.$td),
					'PRO',
					this.$links.getUpsellLink('general-settings-advanced', this.$constants.GLOBAL_STRINGS.learnMore, 'admin-bar-menu', true)
				),
				dashboardWidgetsUpsell : this.$t.sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					this.$t.__('Dashboard Widgets is a %1$s feature. %2$s', this.$td),
					'PRO',
					this.$links.getUpsellLink('general-settings-advanced', this.$constants.GLOBAL_STRINGS.learnMore, 'dashboard-widget', true)
				),
				taxonomyColumns      : this.$t.__('Taxonomy Columns', this.$td),
				includeAllTaxonomies : this.$t.__('Include All Taxonomies', this.$td),
				selectTaxonomies     : this.$t.sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					this.$t.__('Select which Taxonomies you want to use the %1$s columns with.', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				taxonomyColumnsUpsell : this.$t.sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					this.$t.__('Taxonomy Columns is a %1$s feature. %2$s', this.$td),
					'PRO',
					this.$links.getUpsellLink('general-settings-advanced', this.$constants.GLOBAL_STRINGS.learnMore, 'taxonomy-columns', true)
				),
				uninstallAioseo : this.$t.sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					this.$t.__('Uninstall %1$s', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				uninstallAioseoDescription : this.$t.sprintf(
					// Translators: 1 - Plugin Short Name ("AIOSEO").
					this.$t.__('Check this if you would like to remove ALL %1$s data upon plugin deletion. All settings and SEO data will be unrecoverable.', this.$td),
					import.meta.env.VITE_SHORT_NAME
				),
				headlineAnalyzerWarning : this.$t.sprintf(
					// Translators: 1 - "WordPress 5.2", 2 - "Learn More".
					this.$t.__('The Headline Analyzer is only available in %1$s and up. %2$s', this.$td),
					'WordPress 5.2',
					this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'updateWordPress', true)
				),
				openAiKey            : this.$t.__('OpenAI API Key', this.$td),
				openAiKeyDescription : this.$t.sprintf(
					// Translators: 1 - "Learn More" link.
					this.$t.__('Enter an OpenAI API key in order to automatically generate SEO titles and meta descriptions for your pages. %1$s', this.$td),
					this.$links.getDocLink(this.$constants.GLOBAL_STRINGS.learnMore, 'openAi', true)
				),
				openAiKeyUpsell : this.$t.sprintf(
					// Translators: 1 - "PRO", 2 - "Learn more".
					this.$t.__('OpenAI Integration is a %1$s feature. %2$s', this.$td),
					'PRO',
					this.$links.getUpsellLink('general-settings-advanced', this.$constants.GLOBAL_STRINGS.learnMore, 'open-ai', true)
				),
				openAiKeyInvalid : this.$t.__('The API key you have entered is invalid. Please check your API key and try again.', this.$td)
			}
		}
	},
	computed : {
		adminBarMenu : {
			get () {
				return !this.licenseStore.isUnlicensed ? this.optionsStore.options.advanced.adminBarMenu : true
			},
			set (newValue) {
				this.optionsStore.options.advanced.adminBarMenu = newValue
			}
		},
		widgets () {
			return [
				{
					key     : 'seoSetup',
					label   : this.$t.__('SEO Setup Wizard', this.$td),
					tooltip : this.$t.__('Our SEO Setup Wizard dashboard widget helps you remember to finish setting up some initial crucial settings for your site to help you rank higher in search results. Once the setup wizard is completed this widget will automatically disappear.', this.$td)
				},
				{
					key     : 'seoOverview',
					label   : this.$t.__('SEO Overview', this.$td),
					tooltip : this.$t.__('Our SEO Overview widget helps you determine which posts or pages you should focus on for content updates to help you rank higher in search results.', this.$td)
				},
				{
					key     : 'seoNews',
					label   : this.$t.__('SEO News', this.$td),
					tooltip : this.$t.__('Our SEO News widget provides helpful links that enable you to get the most out of your SEO and help you continue to rank higher than your competitors in search results.', this.$td)
				}
			]
		}
	},
	methods : {
		versionCompare : versionCompare,
		updateDashboardWidgets (checked, widget) {
			if (checked) {
				const included = this.optionsStore.options.advanced.dashboardWidgets
				included.push(widget.key)
				this.optionsStore.options.advanced.dashboardWidgets = included
				return
			}

			const index = this.optionsStore.options.advanced.dashboardWidgets.findIndex(t => t === widget.key)
			if (-1 !== index) {
				this.optionsStore.options.advanced.dashboardWidgets.splice(index, 1)
			}
		},
		isDashboardWidgetChecked (widget) {
			return !this.licenseStore.isUnlicensed ? this.optionsStore.options.advanced.dashboardWidgets.includes(widget.key) : true
		},
		validateOpenAiKey () {
			if (this.optionsStore.options.advanced.openAiKey && null === this.optionsStore.options.advanced.openAiKey.match(/^sk-[a-zA-Z0-9]{48}$/)) {
				this.openAiKeyInvalid = true
			} else {
				this.openAiKeyInvalid = false
			}
		}
	},
	beforeMount () {
		this.validateOpenAiKey()
	}
}
</script>

<style lang="scss">
.aioseo-advanced {
	.inline-upsell,
	.warning {
		display: inline-flex;
		margin-top: 12px;
	}

	.aioseo-input-container {
		max-width: 500px;
	}
}
</style>